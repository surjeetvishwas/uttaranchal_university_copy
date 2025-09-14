# Cloud Run Deployment Script for PowerShell
# Run this script to deploy to Google Cloud Run

param(
    [string]$ProjectId = "result-458407",
    [string]$Region = "us-central1",
    [string]$ServiceName = "uttaranchal-university"
)

Write-Host "🚀 Deploying Uttaranchal University to Cloud Run..." -ForegroundColor Green

# Check if gcloud is installed
if (!(Get-Command gcloud -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Google Cloud SDK not found. Please install it first." -ForegroundColor Red
    exit 1
}

# Set the project
Write-Host "📦 Setting project to $ProjectId" -ForegroundColor Blue
gcloud config set project $ProjectId

# Build and deploy to Cloud Run
Write-Host "🔨 Building and deploying to Cloud Run..." -ForegroundColor Blue
gcloud run deploy $ServiceName `
    --source . `
    --platform managed `
    --region $Region `
    --allow-unauthenticated `
    --port 8080 `
    --memory 512Mi `
    --cpu 1 `
    --max-instances 10 `
    --timeout 300 `
    --set-env-vars "ENVIRONMENT=production"

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Deployment successful!" -ForegroundColor Green
    Write-Host "🌐 Your application is now available at:" -ForegroundColor Cyan
    gcloud run services describe $ServiceName --region=$Region --format="value(status.url)"
    
    Write-Host "`n📋 Quick links:" -ForegroundColor Yellow
    $baseUrl = $(gcloud run services describe $ServiceName --region=$Region --format="value(status.url)")
    Write-Host "   🏠 Homepage: $baseUrl" -ForegroundColor White
    Write-Host "   🎓 Student Portal: $baseUrl/result.html" -ForegroundColor White
    Write-Host "   👨‍💼 Admin Panel: $baseUrl/admin.html" -ForegroundColor White
    Write-Host "   🧪 Test Page: $baseUrl/test.html" -ForegroundColor White
    Write-Host "   💚 Health Check: $baseUrl/health" -ForegroundColor White
    
    Write-Host "`n🔐 Admin Credentials:" -ForegroundColor Yellow
    Write-Host "   Username: admin" -ForegroundColor White
    Write-Host "   Password: Admin@123" -ForegroundColor White
} else {
    Write-Host "❌ Deployment failed!" -ForegroundColor Red
    exit 1
}