# Deploy branch-specific item status feature to production
$serverIp = "167.71.51.100"
$serverPath = "/var/www/rayfoodpos"

Write-Host "Deploying branch-specific item status updates..." -ForegroundColor Green

# Upload modified files
Write-Host "`nUploading files to server..." -ForegroundColor Cyan
scp -r app/Services/ItemService.php root@${serverIp}:${serverPath}/app/Services/
scp -r app/Http/Controllers/Admin/ItemController.php root@${serverIp}:${serverPath}/app/Http/Controllers/Admin/
scp -r app/Http/Resources/SimpleItemResource.php root@${serverIp}:${serverPath}/app/Http/Resources/
scp -r app/Http/Resources/NormalItemResource.php root@${serverIp}:${serverPath}/app/Http/Resources/
scp -r resources/js/components/admin/items/ItemListComponent.vue root@${serverIp}:${serverPath}/resources/js/components/admin/items/
scp -r resources/js/components/admin/items/ItemCreateComponent.vue root@${serverIp}:${serverPath}/resources/js/components/admin/items/
scp -r resources/js/languages/az.json root@${serverIp}:${serverPath}/resources/js/languages/

# Build assets on server
Write-Host "`nBuilding assets on server..." -ForegroundColor Cyan
ssh root@${serverIp} "cd ${serverPath} && npm run build"

Write-Host "`nDeployment complete!" -ForegroundColor Green
Write-Host "`nFeatures deployed:" -ForegroundColor Yellow
Write-Host "  ✓ Branch-specific item status toggle in items list"
Write-Host "  ✓ Branch status controls in item edit form"
Write-Host "  ✓ POS and Menu pages respect branch-specific statuses"
Write-Host "  ✓ Each branch has independent item visibility"

