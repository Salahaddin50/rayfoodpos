# 🌐 Setting Up pos.rayfood.az Subdomain

## Complete Guide for DigitalOcean + Domain Configuration

---

## STEP 1: DNS Configuration

### Option A: Using DigitalOcean DNS (Recommended)

1. **Log in to your Domain Registrar** (where you bought rayfood.az)

2. **Update Nameservers** to point to DigitalOcean:
   ```
   ns1.digitalocean.com
   ns2.digitalocean.com
   ns3.digitalocean.com
   ```

3. **In DigitalOcean Dashboard:**
   - Go to **Networking** → **Domains**
   - Click "Add Domain"
   - Enter: `rayfood.az`
   - Select your droplet: `167.71.51.100`

4. **Add DNS Records:**
   
   **Record 1 (Main Domain):**
   - Type: `A`
   - Hostname: `@`
   - Value: `167.71.51.100`
   - TTL: 3600
   
   **Record 2 (POS Subdomain):**
   - Type: `A`
   - Hostname: `pos`
   - Value: `167.71.51.100`
   - TTL: 3600
   
   **Record 3 (WWW - Optional):**
   - Type: `CNAME`
   - Hostname: `www`
   - Value: `@`
   - TTL: 3600

### Option B: Using Your Domain Registrar's DNS

If you prefer to keep DNS at your registrar:

1. **Log in to your Domain Registrar**
2. **Go to DNS Management** for `rayfood.az`
3. **Add these records:**

   ```
   Type: A
   Host: pos
   Points to: 167.71.51.100
   TTL: 3600
   ```

---

## STEP 2: Deploy Application to Server

Run the automated deployment script:

### On Windows (PowerShell):
```powershell
.\deploy-pos-subdomain.ps1
```

### On Linux/Mac:
```bash
chmod +x deploy-pos-subdomain.sh
./deploy-pos-subdomain.sh
```

---

## STEP 3: Verify DNS Propagation

Wait 5-10 minutes for DNS to propagate, then check:

### Method 1: Using Command Line
```bash
# Windows PowerShell
nslookup pos.rayfood.az

# Linux/Mac
dig pos.rayfood.az
```

### Method 2: Online Tools
- Visit: https://dnschecker.org
- Enter: `pos.rayfood.az`
- Check if it shows: `167.71.51.100`

---

## STEP 4: Access Your Application

Once DNS propagates:

- **HTTP**: http://pos.rayfood.az
- **HTTPS**: https://pos.rayfood.az (after SSL setup completes)

---

## STEP 5: SSL Certificate Setup

The deployment script will automatically:
1. Install Certbot (Let's Encrypt client)
2. Request SSL certificate for `pos.rayfood.az`
3. Configure auto-renewal
4. Redirect HTTP to HTTPS

**Note**: SSL setup requires DNS to be working first!

---

## Troubleshooting

### Issue: DNS not resolving
- **Solution**: Wait 15-30 minutes for full propagation
- Clear DNS cache: `ipconfig /flushdns` (Windows) or `sudo systemd-resolve --flush-caches` (Linux)

### Issue: SSL certificate fails
- **Solution**: Ensure DNS is working first (wait for propagation)
- Check firewall allows ports 80 and 443
- Re-run: `sudo certbot --nginx -d pos.rayfood.az`

### Issue: 502 Bad Gateway
- **Solution**: Check PHP-FPM is running: `sudo systemctl status php8.2-fpm`
- Restart services: `sudo systemctl restart php8.2-fpm nginx`

### Issue: Permission denied errors
- **Solution**: Fix permissions:
  ```bash
  sudo chown -R www-data:www-data /var/www/rayfoodpos
  sudo chmod -R 755 /var/www/rayfoodpos
  sudo chmod -R 775 /var/www/rayfoodpos/storage /var/www/rayfoodpos/bootstrap/cache
  ```

---

## Summary Checklist

- [ ] DNS records added (A record for `pos` pointing to `167.71.51.100`)
- [ ] Deployment script executed successfully
- [ ] DNS propagation verified
- [ ] Application accessible via browser
- [ ] SSL certificate installed and HTTPS working
- [ ] Database connected and working

---

## Important URLs

- **Application**: https://pos.rayfood.az
- **Server IP**: http://167.71.51.100
- **DNS Checker**: https://dnschecker.org
- **SSL Checker**: https://www.sslshopper.com/ssl-checker.html

---

## Need Help?

If you encounter issues:
1. Check the deployment log
2. Review Nginx error logs: `sudo tail -f /var/log/nginx/error.log`
3. Check Laravel logs: `sudo tail -f /var/www/rayfoodpos/storage/logs/laravel.log`

