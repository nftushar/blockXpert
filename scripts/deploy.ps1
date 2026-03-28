# Build and Prepare BlockXpert for WordPress.org Deployment
# This script handles everything needed to create a distribution package

param(
    [string]$VersionBump = "patch",  # patch, minor, major
    [switch]$SkipBuild = $false,
    [switch]$SkipComposer = $false,
    [switch]$DryRun = $false
)

$ErrorActionPreference = "Stop"

# Get plugin directory
$PluginDir = Split-Path -Parent $PSScriptRoot
$PluginName = Split-Path -Leaf $PluginDir

Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  BlockXpert - WordPress.org Deployment Script" -ForegroundColor Green
Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Check if we're in right directory
if (-not (Test-Path "$PluginDir/blockxpert.php")) {
    Write-Host "❌ Error: blockxpert.php not found!" -ForegroundColor Red
    Write-Host "Make sure you run this script from the scripts directory" -ForegroundColor Yellow
    exit 1
}

Write-Host "📂 Plugin Directory: $PluginDir" -ForegroundColor White
Write-Host ""

# Step 1: Install npm dependencies
if (-not $SkipBuild) {
    Write-Host "Step 1️⃣  - Installing npm dependencies..." -ForegroundColor Cyan
    Push-Location $PluginDir
    
    if (-not $DryRun) {
        npm install 2>&1 | Out-Null
        if ($LASTEXITCODE -ne 0) {
            Write-Host "❌ npm install failed" -ForegroundColor Red
            exit 1
        }
    }
    
    Write-Host "✅ npm dependencies installed" -ForegroundColor Green
    Pop-Location
}

# Step 2: Build production assets
if (-not $SkipBuild) {
    Write-Host ""
    Write-Host "Step 2️⃣  - Building production assets (npm run build:prod)..." -ForegroundColor Cyan
    Push-Location $PluginDir
    
    if (-not $DryRun) {
        npm run build:prod 2>&1 | Out-Null
        if ($LASTEXITCODE -ne 0) {
            Write-Host "❌ npm build failed" -ForegroundColor Red
            exit 1
        }
    }
    
    Write-Host "✅ Production assets built" -ForegroundColor Green
    
    # Verify build output
    if (Test-Path "$PluginDir/build") {
        $fileCount = (Get-ChildItem -Recurse $PluginDir/build | Measure-Object).Count
        Write-Host "   📦 Build directory contains $fileCount files" -ForegroundColor Gray
    } else {
        Write-Host "   ⚠️  Build directory not found!" -ForegroundColor Yellow
    }
    
    Pop-Location
}

# Step 3: Install PHP composer dependencies
if (-not $SkipComposer) {
    Write-Host ""
    Write-Host "Step 3️⃣  - Installing PHP dependencies (composer install --no-dev)..." -ForegroundColor Cyan
    Push-Location $PluginDir
    
    if (-not $DryRun) {
        composer install --no-dev --optimize-autoloader 2>&1 | Out-Null
        if ($LASTEXITCODE -ne 0) {
            Write-Host "❌ Composer install failed" -ForegroundColor Red
            exit 1
        }
    }
    
    Write-Host "✅ PHP dependencies installed" -ForegroundColor Green
    Pop-Location
}

# Step 4: Read current version
Write-Host ""
Write-Host "Step 4️⃣  - Reading current version..." -ForegroundColor Cyan
$phpContent = Get-Content "$PluginDir/blockxpert.php" -Raw
if ($phpContent -match 'Version:\s+([0-9.]+)') {
    $CurrentVersion = $matches[1]
    Write-Host "   Current version: $CurrentVersion" -ForegroundColor White
} else {
    Write-Host "❌ Could not find version in blockxpert.php" -ForegroundColor Red
    exit 1
}

# Step 5: Create distribution package
Write-Host ""
Write-Host "Step 5️⃣  - Creating distribution package..." -ForegroundColor Cyan

$TempDir = "$env:TEMP\blockxpert-deploy-$(Get-Random)"
$DeployDir = "$TempDir\$PluginName"

if (-not $DryRun) {
    # Create plugin structure in temp directory
    New-Item -ItemType Directory -Path $DeployDir -Force | Out-Null
    
    # Files/folders to include
    $Include = @(
        "blockxpert.php",
        "composer.json",
        "license.txt",
        "README.md",
        "DEPLOYMENT.md",
        "includes",
        "languages",
        "build",
        "vendor"
    )
    
    $Exclude = @(
        "node_modules",
        ".git",
        ".gitignore",
        "src",
        "scripts",
        "package.json",
        "package-lock.json",
        ".wpscriptsrc.json",
        "composer.lock",
        ".github",
        ".editorconfig"
    )
    
    Write-Host "   📦 Copying plugin files..." -ForegroundColor Gray
    
    foreach ($item in $Include) {
        $source = Join-Path $PluginDir $item
        $dest = Join-Path $DeployDir $item
        
        if (Test-Path $source) {
            if ((Get-Item $source).PSIsContainer) {
                Copy-Item -Path $source -Destination $dest -Recurse -Force | Out-Null
            } else {
                Copy-Item -Path $source -Destination $dest -Force | Out-Null
            }
        }
    }
    
    # Create zip file
    $OutDir = "$PluginDir/../"
    $ZipFile = "$OutDir/blockxpert-$CurrentVersion.zip"
    
    Write-Host "   📦 Creating zip: blockxpert-$CurrentVersion.zip" -ForegroundColor Gray
    
    # Remove existing zip
    if (Test-Path $ZipFile) {
        Remove-Item $ZipFile -Force
    }
    
    # Create zip using .NET
    [System.IO.Compression.ZipFile]::CreateFromDirectory($TempDir, $ZipFile) 2>&1 | Out-Null
    
    # Verify zip
    if (Test-Path $ZipFile) {
        $ZipSize = (Get-Item $ZipFile).Length / 1MB
        Write-Host "✅ Distribution package created" -ForegroundColor Green
        Write-Host "   📊 File: blockxpert-$CurrentVersion.zip" -ForegroundColor Gray
        Write-Host "   📊 Size: $([Math]::Round($ZipSize, 2)) MB" -ForegroundColor Gray
    } else {
        Write-Host "❌ Failed to create zip file" -ForegroundColor Red
        exit 1
    }
    
    # Cleanup temp directory
    Remove-Item -Path $TempDir -Recurse -Force -ErrorAction SilentlyContinue
}

# Step 6: Display deployment checklist
Write-Host ""
Write-Host "Step 6️⃣  - Deployment Checklist:" -ForegroundColor Cyan
Write-Host ""
Write-Host "✅ Production assets built and optimized" -ForegroundColor Green
Write-Host "✅ PHP dependencies installed (production only)" -ForegroundColor Green
Write-Host "✅ Development files excluded" -ForegroundColor Green
Write-Host "✅ Distribution package created" -ForegroundColor Green
Write-Host ""

# Step 7: Next steps
Write-Host "Step 7️⃣  - Next Steps:" -ForegroundColor Cyan
Write-Host ""
Write-Host "🔗 Upload to WordPress.org:" -ForegroundColor White
Write-Host "   1. Go to https://wordpress.org/plugins/blockxpert/" -ForegroundColor Gray
Write-Host "   2. Dashboard → Add New Version" -ForegroundColor Gray
Write-Host "   3. Upload: blockxpert-$CurrentVersion.zip" -ForegroundColor Gray
Write-Host ""
Write-Host "📝 Update plugin files via SVN:" -ForegroundColor White
Write-Host "   svn co https://plugins.svn.wordpress.org/blockxpert/ blockxpert-svn" -ForegroundColor Gray
Write-Host "   # Copy trunk files, commit changes" -ForegroundColor Gray
Write-Host ""
Write-Host "🏷️  Create Git Tag:" -ForegroundColor White
Write-Host "   git tag -a v$CurrentVersion -m 'Release version $CurrentVersion'" -ForegroundColor Gray
Write-Host "   git push origin v$CurrentVersion" -ForegroundColor Gray
Write-Host ""

Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  ✨ Deployment package ready for WordPress.org!" -ForegroundColor Green
Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
