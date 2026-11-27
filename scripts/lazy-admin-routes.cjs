const fs = require('fs');
const path = require('path');

const dir = path.join('resources', 'js', 'router', 'modules');
const admin = [
  'administratorRoutes.js',
  'adminTableOrderRoutes.js',
  'campaignRoutes.js',
  'chefRoutes.js',
  'creditBalanceReportRoutes.js',
  'customerRoutes.js',
  'diningTableRoutes.js',
  'driverRoutes.js',
  'employeeRoutes.js',
  'itemRoutes.js',
  'itemsReportRoutes.js',
  'kitchenDisplaySystemRoutes.js',
  'offerRoutes.js',
  'onlineUserRoutes.js',
  'orderStatusScreenRoutes.js',
  'posOrderRoutes.js',
  'posRoutes.js',
  'profileRoutes.js',
  'salesReportRoutes.js',
  'settingRoutes.js',
  'takeawayTypeRoutes.js',
  'transactionRoutes.js',
  'waiterRoutes.js',
];

for (const name of admin) {
  const p = path.join(dir, name);
  if (!fs.existsSync(p)) continue;

  let c = fs.readFileSync(p, 'utf8');
  const re = /import\s+(\w+)\s+from\s+["']([^"']+)["'];?/g;
  const imports = [];
  let m;
  while ((m = re.exec(c))) {
    imports.push({ full: m[0], v: m[1], imp: m[2] });
  }

  for (const i of imports) {
    c = c.split(i.full).join('');
    c = c.replace(
      new RegExp('component:\\s*' + i.v + '\\b', 'g'),
      'component: () => import("' + i.imp + '")'
    );
  }

  c = c.replace(/^(\r?\n)+/, '');
  fs.writeFileSync(p, c);
  console.log('Converted', name);
}
