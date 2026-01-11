# 🌐 DNS Configuration Guide for pos.rayfood.az

This guide shows you exactly how to configure DNS for your subdomain.

---

## Your Server Details

- **Droplet IP**: `167.71.51.100`
- **Domain**: `rayfood.az`
- **Subdomain**: `pos.rayfood.az`

---

## Method 1: Using DigitalOcean DNS (Recommended)

### Step 1: Update Nameservers at Domain Registrar

1. Log in to where you purchased `rayfood.az` (GoDaddy, Namecheap, etc.)
2. Find "Nameservers" or "DNS Management" settings
3. Change nameservers to:
   ```
   ns1.digitalocean.com
   ns2.digitalocean.com
   ns3.digitalocean.com
   ```
4. Save changes (propagation takes 1-24 hours, usually under 1 hour)

### Step 2: Configure DNS in DigitalOcean

1. **Go to DigitalOcean Dashboard**
   - URL: https://cloud.digitalocean.com

2. **Navigate to Networking**
   - Click "Networking" in left sidebar
   - Click "Domains" tab

3. **Add Your Domain**
   - Click "Add Domain" button
   - Enter: `rayfood.az`
   - Select your droplet from dropdown
   - Click "Add Domain"

4. **Add DNS Records**

   Click "Add Record" for each:

   **Record 1: Main Domain**
   ```
   Type: A
   Hostname: @
   Will direct to: [Your Droplet]
   TTL: 3600
   ```

   **Record 2: POS Subdomain** ⭐ **REQUIRED**
   ```
   Type: A
   Hostname: pos
   Will direct to: [Your Droplet]
   TTL: 3600
   ```

   **Record 3: WWW Subdomain** (Optional)
   ```
   Type: CNAME
   Hostname: www
   Is an alias of: @
   TTL: 3600
   ```

5. **Final Result Should Look Like:**
   ```
   @       A       167.71.51.100   3600
   pos     A       167.71.51.100   3600
   www     CNAME   @               3600
   ```

---

## Method 2: Using Domain Registrar's DNS

If you prefer to keep DNS at your registrar (GoDaddy, Namecheap, etc.):

### For GoDaddy:

1. Log in to GoDaddy account
2. Go to "My Products" → "Domains"
3. Click on `rayfood.az`
4. Click "Manage DNS"
5. Click "Add" under Records section
6. Add this record:
   ```
   Type: A
   Name: pos
   Value: 167.71.51.100
   TTL: 1 Hour
   ```
7. Click "Save"

### For Namecheap:

1. Log in to Namecheap account
2. Go to "Domain List"
3. Click "Manage" next to `rayfood.az`
4. Go to "Advanced DNS" tab
5. Click "Add New Record"
6. Add this record:
   ```
   Type: A Record
   Host: pos
   Value: 167.71.51.100
   TTL: Automatic
   ```
7. Click the checkmark to save

### For Cloudflare:

1. Log in to Cloudflare dashboard
2. Select domain `rayfood.az`
3. Go to "DNS" tab
4. Click "Add record"
5. Add this record:
   ```
   Type: A
   Name: pos
   IPv4 address: 167.71.51.100
   Proxy status: DNS only (gray cloud) ⚠️ Important!
   TTL: Auto
   ```
6. Click "Save"

**Note**: For Cloudflare, use "DNS only" (gray cloud) initially. After SSL is working, you can enable proxy (orange cloud).

### For Other Registrars:

General steps for any registrar:
1. Find "DNS Management" or "DNS Settings"
2. Add a new A record:
   - **Type**: A
   - **Host/Name**: pos
   - **Points to/Value**: 167.71.51.100
   - **TTL**: 3600 or Automatic

---

## Verify DNS Configuration

### Method 1: Command Line

**Windows (PowerShell):**
```powershell
nslookup pos.rayfood.az
```

**Linux/Mac:**
```bash
dig pos.rayfood.az
```

**Expected Result:**
```
Name:    pos.rayfood.az
Address: 167.71.51.100
```

### Method 2: Online Tools

1. **DNS Checker** (Shows propagation worldwide)
   - Visit: https://dnschecker.org
   - Enter: `pos.rayfood.az`
   - Should show: `167.71.51.100` in green checkmarks

2. **What's My DNS**
   - Visit: https://whatsmydns.net
   - Enter: `pos.rayfood.az`
   - Type: A
   - Should show: `167.71.51.100` globally

### Method 3: Browser Test

After DNS propagates, visit:
```
http://pos.rayfood.az
```

If you see your application or a server response, DNS is working!

---

## DNS Propagation Timeline

- **Local Cache**: Immediate (your computer)
- **ISP DNS**: 5-30 minutes
- **Global DNS**: 1-24 hours (usually 1-4 hours)
- **Full Propagation**: Up to 48 hours (rare)

### Speed Up DNS Changes:

1. **Use short TTL**: Set TTL to 300 (5 minutes) before making changes
2. **Clear local DNS cache**:
   ```powershell
   # Windows
   ipconfig /flushdns
   ```
   ```bash
   # Linux
   sudo systemd-resolve --flush-caches
   ```
   ```bash
   # Mac
   sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder
   ```

---

## Troubleshooting

### Issue: "DNS_PROBE_FINISHED_NXDOMAIN"
**Solution**: DNS not configured or still propagating
- Double-check DNS records are correct
- Wait 15-30 minutes
- Try incognito/private browser window

### Issue: DNS shows different IP
**Solution**: Old DNS cache
- Clear DNS cache (commands above)
- Wait for propagation
- Try from different network/device

### Issue: "This site can't be reached"
**Solution**: Either DNS or server issue
- Check DNS: `nslookup pos.rayfood.az`
- Check server: `ping 167.71.51.100`
- Check firewall allows ports 80 and 443

### Issue: SSL certificate error
**Solution**: Wait for DNS first!
- SSL requires working DNS
- After DNS works, run: `sudo certbot --nginx -d pos.rayfood.az`

---

## Quick Reference

### DNS Record to Add:
```
Type: A
Host: pos
Points to: 167.71.51.100
TTL: 3600
```

### Verification Command:
```bash
nslookup pos.rayfood.az
```

### Expected Result:
```
pos.rayfood.az = 167.71.51.100
```

### After DNS Works:
1. Run deployment script
2. Wait for SSL to install
3. Access: https://pos.rayfood.az

---

## Need Help?

If DNS is not working after 30 minutes:
1. Screenshot your DNS settings
2. Run: `nslookup pos.rayfood.az`
3. Share the output for troubleshooting

---

## Summary Checklist

- [ ] DNS A record added for `pos` pointing to `167.71.51.100`
- [ ] Waited 5-10 minutes for propagation
- [ ] Verified with `nslookup pos.rayfood.az`
- [ ] Shows correct IP address
- [ ] Ready to run deployment script

Once DNS is working, proceed with deployment! ✅

