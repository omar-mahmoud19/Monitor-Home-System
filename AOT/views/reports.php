<?php
require_once __DIR__ . '/../api/session.php';
checkRole(['owner', 'tenant']);
$user = getCurrentUser();
if ($user) unset($user['password']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reports — AOT Homes</title>
  <link rel="stylesheet" href="/AOT/assets/css/style.css" />
  <script src="/AOT/assets/js/main.js" defer></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
</head>

<body>
  <div class="layout">
    <aside class="sidebar">
      <div class="sidebar__logo">
        <div class="sidebar__logo-icon">🏠</div>
        <div>
          <div class="sidebar__logo-text">AOT Homes</div>
          <div class="sidebar__logo-sub">Energy Monitor</div>
        </div>
      </div>
      <nav class="sidebar__nav">
        <span class="nav__group-label">Overview</span>
        <a href="/AOT/index.php" class="nav__item"><span class="icon">⚡</span> Dashboard</a>

        <?php if (in_array($user['role'], ['owner', 'tenant'])): ?>
          <span class="nav__group-label">Management</span>
          <a href="/AOT/appliances.php" class="nav__item"><span class="icon">🔌</span> Appliances</a>
          <a href="/AOT/goals.php" class="nav__item"><span class="icon">🎯</span> Goals &amp; Budget</a>
        <?php endif; ?>

        <?php if ($user['role'] === 'owner'): ?>
          <a href="/AOT/automation.php" class="nav__item"><span class="icon">🤖</span> Automation</a>
        <?php endif; ?>

        <span class="nav__group-label">System</span>
        <?php if (in_array($user['role'], ['owner', 'tenant'])): ?>
          <a href="/AOT/reports.php" class="nav__item"><span class="icon">📊</span> Reports</a>
        <?php endif; ?>

        <?php if ($user['role'] === 'owner'): ?>
          <a href="/AOT/settings.php" class="nav__item"><span class="icon">⚙️</span> Settings</a>
        <?php endif; ?>
      </nav>
      <div class="sidebar__footer">
        <div class="sidebar__avatar" id="sidebar-avatar">AO</div>
        <div>
          <div class="sidebar__user-name" id="sidebar-name">Home Owner</div>
          <div class="sidebar__user-role" id="sidebar-role">owner</div>
        </div>
        <button class="sidebar__logout" id="logout-btn" title="Sign Out"
          style="margin-left:auto;background:none;border:none;cursor:pointer;font-size:1rem;color:var(--text-3)">✕</button>
      </div>
    </aside>

    <main class="main">
      <header class="topbar">
        <h1 class="topbar__title">Reports &amp; History</h1>
        <span class="topbar__meta" id="today-date"></span>
        <span class="topbar__meta" id="live-clock"></span>
        <button class="topbar__btn" id="export-csv-btn">⬇ Export CSV</button>
        <button class="topbar__btn primary" id="export-pdf-btn">⬇ Export PDF</button>
      </header>

      <div class="content">

        <!-- Date Range Filter -->
        <div class="card mb-lg">
          <div style="display:flex;align-items:center;gap:var(--sp-md);flex-wrap:wrap">
            <span class="text-sm font-head">Filter Range:</span>
            <input class="form-input" type="date" id="date-from" name="date_from" style="width:160px" />
            <span class="text-muted">to</span>
            <input class="form-input" type="date" id="date-to" name="date_to" style="width:160px" />
            <select class="form-select" id="resource-filter" style="width:auto;padding:9px 28px 9px 10px">
              <option value="">All Resources</option>
              <option value="electricity">Electricity</option>
              <option value="water">Water</option>
              <option value="gas">Gas</option>
            </select>
            <button class="btn btn--primary btn--sm" id="apply-filter-btn">Apply Filter</button>
            <button class="btn btn--outline btn--sm" data-preset="this_month">This Month</button>
            <button class="btn btn--outline btn--sm" data-preset="last_30">Last 30 Days</button>
            <button class="btn btn--outline btn--sm" data-preset="last_90">Last 3 Months</button>
          </div>
        </div>

        <!-- Monthly Trend Chart -->
        <div class="card mb-lg">
          <div class="section-header">
            <div>
              <div class="section-title">Monthly Usage Trend — Last 6 Months</div>
              <div class="section-sub" id="chart-status">Loading from API…</div>
            </div>
          </div>
          <div class="chart-wrap" style="height:280px">
            <canvas id="chart-monthly"></canvas>
          </div>
        </div>

        <!-- Monthly Summary Table -->
        <div class="card mb-lg">
          <div class="section-header">
            <div class="section-title">Monthly Summary Table</div>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Month</th>
                  <th>Electricity (kWh)</th>
                  <th>Water (L)</th>
                  <th>Gas (m³)</th>
                  <th>Solar (kWh)</th>
                  <th>Total Cost ($)</th>
                  <th>CO₂ (kg)</th>
                  <th>vs Budget</th>
                  <th>vs Prev Month</th>
                </tr>
              </thead>
              <tbody id="monthly-table-body">
                <tr>
                  <td colspan="8" class="text-muted" style="text-align:center;padding:var(--sp-lg)">Loading…</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Audit Trail -->
        <div class="card">
          <div class="section-header">
            <div>
              <div class="section-title">System Audit Trail</div>
              <div class="section-sub" id="audit-status">Loading recent activity…</div>
            </div>
            <select class="form-select" id="audit-type-filter"
              style="width:auto;padding:6px 28px 6px 10px;font-size:0.78rem">
              <option value="">All Events</option>
              <option value="setting">Settings Changes</option>
              <option value="appliance">Appliance Events</option>
              <option value="alert">Alerts</option>
              <option value="user">User Actions</option>
            </select>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Timestamp</th>
                  <th>User</th>
                  <th>Event Type</th>
                  <th>Description</th>
                  <th>IP Address</th>
                </tr>
              </thead>
              <tbody id="audit-table-body">
                <tr>
                  <td colspan="5" class="text-muted" style="text-align:center;padding:var(--sp-lg)">Loading…</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div><!-- /content -->
    </main>
  </div><!-- /layout -->

  <script>
    /**
     * ─────────────────────────────────────────────────────────────
     *  reports.php — Client-side integration with api/reports.php
     * ─────────────────────────────────────────────────────────────
     */

    /* ── Utility: POST to api/reports.php ── */
    async function apiReports(body = {}) {
      const fd = new FormData();
      for (const [k, v] of Object.entries(body)) fd.append(k, v);
      const res = await fetch('/AOT/api/reports.php', {
        method: 'POST',
        body: fd
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    }

    /* ── Utility: GET from api/reports.php ── */
    async function apiReportsGet(params = {}) {
      const qs = new URLSearchParams(params).toString();
      const res = await fetch(`/AOT/api/reports.php?${qs}`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    }

    /* ── Date helpers ── */
    function toYMD(d) {
      return d.toISOString().slice(0, 10);
    }

    function applyPreset(preset) {
      const today = new Date();
      let from, to = toYMD(today);
      if (preset === 'this_month') {
        from = toYMD(new Date(today.getFullYear(), today.getMonth(), 1));
      } else if (preset === 'last_30') {
        const d = new Date(today);
        d.setDate(d.getDate() - 30);
        from = toYMD(d);
      } else if (preset === 'last_90') {
        const d = new Date(today);
        d.setDate(d.getDate() - 90);
        from = toYMD(d);
      }
      document.getElementById('date-from').value = from;
      document.getElementById('date-to').value = to;
      loadReport(from, to);
    }

    /* ── Format timestamp ── */
    function fmtDate(ts) {
      if (!ts) return '—';
      const d = new Date(ts);
      const today = new Date();
      const yesterday = new Date(today);
      yesterday.setDate(today.getDate() - 1);
      const sameDay = (a, b) => a.toDateString() === b.toDateString();
      const time = d.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
      });
      if (sameDay(d, today)) return `Today ${time}`;
      if (sameDay(d, yesterday)) return `Yesterday ${time}`;
      return d.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: '2-digit'
      }) + ` ${time}`;
    }

    /* ── Event-type badge mapping ── */
    function eventBadge(type) {
      const map = {
        alert: 'badge--danger',
        setting: 'badge--info',
        appliance: 'badge--ok',
        goal_create: 'badge--ok',
        goal_update: 'badge--info',
        goal_delete: 'badge--danger',
        report_generate: 'badge--info',
        report_delete: 'badge--danger',
      };
      const label = type?.replace(/_/g, ' ') || 'event';
      const cls = Object.keys(map).find(k => type?.includes(k));
      return `<span class="badge ${map[cls] || 'badge--neutral'}">${label}</span>`;
    }

    /* ── Chart instance (kept so we can destroy/rebuild) ── */
    let monthlyChart = null;

    /* ─────────────────────────────────────────────────────────
     *  Load report: POST action=generate → render chart + table
     * ───────────────────────────────────────────────────────── */
    async function loadReport(from, to, type = 'custom') {
      const statusEl = document.getElementById('chart-status');
      statusEl.textContent = 'Loading…';

      try {
        const data = await apiReports({
          action: 'generate',
          from,
          to,
          type
        });
        if (!data.ok) throw new Error(data.msg || 'API error');

        const report = data.report;
        const dataJson = typeof report.data_json === 'string' ?
          JSON.parse(report.data_json) :
          (report.data_json || {});
        const monthly = dataJson.monthly || [];

        statusEl.textContent = `${from} → ${to} · ${monthly.length} months`;

        renderChart(monthly);
        renderTable(monthly);
      } catch (err) {
        statusEl.textContent = `⚠ Could not load report`;
        console.error('loadReport:', err);
        /* Fallback: show static placeholder data */
        renderChartFallback();
        renderTableFallback();
      }
    }

    /* ─────────────────────────────────────────────────────────
     *  Render Chart.js bar chart from monthly data
     * ───────────────────────────────────────────────────────── */
    function renderChart(monthly) {
      if (monthlyChart) {
        monthlyChart.destroy();
        monthlyChart = null;
      }

      const labels = monthly.map(r => r.label || r.month || '—');
      const elec = monthly.map(r => r.electricity_kwh ?? r.electricity ?? 0);
      const water = monthly.map(r => (r.water_liters ?? r.water ?? 0) / 100);
      const gas = monthly.map(r => r.gas_m3 ?? r.gas ?? 0);
      const solar = monthly.map(r => r.solar_kwh ?? r.solar ?? 0); // ← ADD

      try {
        monthlyChart = makeBarChart('chart-monthly', {
          labels,
          datasets: [{
              label: 'Electricity (kWh)',
              data: elec,
              color: '#60a5fa'
            },
            {
              label: 'Water (×100 L)',
              data: water,
              color: '#34d399'
            },
            {
              label: 'Gas (m³)',
              data: gas,
              color: '#fb923c'
            },
            {
              label: 'Solar (kWh)',
              data: solar,
              color: '#facc15'
            }, // ← ADD
          ],
        });
      } catch (e) {
        // Chart.js direct fallback
        const ctx = document.getElementById('chart-monthly').getContext('2d');
        monthlyChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels,
            datasets: [{
                label: 'Electricity (kWh)',
                data: elec,
                backgroundColor: '#60a5fa'
              },
              {
                label: 'Water (×100 L)',
                data: water,
                backgroundColor: '#34d399'
              },
              {
                label: 'Gas (m³)',
                data: gas,
                backgroundColor: '#fb923c'
              },
              {
                label: 'Solar (kWh)',
                data: solar,
                backgroundColor: '#facc15'
              }, // ← ADD
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false
          },
        });
      }
    }
    /* ─────────────────────────────────────────────────────────
     *  Render monthly summary table
     * ───────────────────────────────────────────────────────── */
    function renderTable(monthly) {
      const tbody = document.getElementById('monthly-table-body');
      if (!monthly.length) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-muted" style="text-align:center;padding:var(--sp-lg)">No data for selected range.</td></tr>`;
        return;
      }

      // Get currency symbol from settings (synced from api/settings.php)
      const settings = (typeof getSettings === 'function') ? getSettings() : {
        currency: '$'
      };
      const currencySymbol = settings.currency || '$';

      // Get goals budgets to calculate real budget_pct
      // PHP: GoalDB reads from api/goals.php — here we use localStorage which is synced
      const goals = (typeof GoalDB !== 'undefined') ? GoalDB.getAll() : [];
      const totalBudget = goals.reduce((sum, g) => sum + (g.budget || 0), 0);

      tbody.innerHTML = monthly.map((r, i) => {
        const prev = monthly[i + 1];
        const cost = parseFloat(r.total_cost ?? r.cost ?? 0).toFixed(0);
        const co2 = parseFloat(r.co2_kg ?? r.co2 ?? 0).toFixed(0);

        // Real budget_pct: use actual cost vs total monthly budget from Goals
        let pct;
        if (totalBudget > 0) {
          pct = parseFloat(((parseFloat(cost) / totalBudget) * 100).toFixed(1));
        } else {
          pct = parseFloat(r.budget_pct ?? r.budget_percent ?? 0);
        }

        const budgeCls = pct >= 90 ? 'badge--danger' : pct >= 70 ? 'badge--warn' : 'badge--ok';
        const budgeLabel = pct >= 90 ? 'Over' : pct >= 70 ? 'Near' : 'Under';

        let vsLabel = '—';
        if (prev) {
          const prevCost = parseFloat(prev.total_cost ?? prev.cost ?? 0);
          const currCost = parseFloat(r.total_cost ?? r.cost ?? 0);
          if (prevCost > 0) {
            const diff = ((currCost - prevCost) / prevCost * 100).toFixed(0);
            const cls = diff > 0 ? 'text-danger' : 'text-accent';
            vsLabel = `<span class="${cls}">${diff > 0 ? '▲' : '▼'} ${Math.abs(diff)}%</span>`;
          }
        }

        return `
      <tr>
        <td><b>${r.label || r.month || '—'}</b></td>
        <td>${(r.electricity_kwh ?? r.electricity ?? 0).toLocaleString()}</td>
        <td>${(r.water_liters   ?? r.water        ?? 0).toLocaleString()}</td>
        <td>${(r.gas_m3         ?? r.gas          ?? 0).toLocaleString()}</td>
        <td>${(r.solar_kwh ?? r.solar ?? 0).toLocaleString()}</td>
        <td>${currencySymbol}${cost}</td>
        <td>${co2}</td>
        <td><span class="badge ${budgeCls}">${budgeLabel} (${pct}%)</span></td>
        <td>${vsLabel}</td>
      </tr>`;
      }).join('');
    }

    /* ─────────────────────────────────────────────────────────
     *  Load audit trail: GET action=activity
     * ───────────────────────────────────────────────────────── */
    async function loadAuditTrail(typeFilter = '') {
      const statusEl = document.getElementById('audit-status');
      statusEl.textContent = 'Loading…';

      try {
        const data = await apiReportsGet({
          action: 'activity',
          limit: 50,
          days: 90
        });
        if (!data.ok) throw new Error(data.msg || 'API error');

        let rows = data.recent || [];
        if (typeFilter) rows = rows.filter(r => (r.action || r.event_type || '').includes(typeFilter));

        statusEl.textContent = `${rows.length} events`;
        renderAuditTable(rows);
      } catch (err) {
        statusEl.textContent = `⚠ Could not load audit trail`;
        console.error('loadAuditTrail:', err);
        renderAuditFallback();
      }
    }

    function renderAuditTable(rows) {
      const tbody = document.getElementById('audit-table-body');
      if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-muted" style="text-align:center;padding:var(--sp-lg)">No events found.</td></tr>`;
        return;
      }
      tbody.innerHTML = rows.map(r => `
    <tr>
      <td class="text-muted">${fmtDate(r.created_at || r.timestamp)}</td>
      <td>${r.user_name || r.username || 'System'}</td>
      <td>${eventBadge(r.action || r.event_type)}</td>
      <td>${r.description || r.details || '—'}</td>
      <td class="text-muted">${r.ip_address || '—'}</td>
    </tr>`).join('');
    }

    /* ─────────────────────────────────────────────────────────
     *  Export buttons → api/reports.php?format=csv|pdf
     * ───────────────────────────────────────────────────────── */
    function getDateRange() {
      return {
        from: document.getElementById('date-from').value || '',
        to: document.getElementById('date-to').value || '',
      };
    }

    document.getElementById('export-csv-btn')?.addEventListener('click', () => {
      const {
        from,
        to
      } = getDateRange();
      window.location.href = `/AOT/api/reports.php?format=csv&from=${from}&to=${to}`;
    });

    document.getElementById('export-pdf-btn')?.addEventListener('click', () => {
      const {
        from,
        to
      } = getDateRange();
      window.location.href = `/AOT/api/reports.php?format=pdf&from=${from}&to=${to}`;
    });

    /* ─────────────────────────────────────────────────────────
     *  Fallback static data (if API unavailable)
     * ───────────────────────────────────────────────────────── */
    function renderChartFallback() {
      try {
        const fallback = [{
            label: 'Nov 25',
            electricity_kwh: 434,
            water_liters: 36800,
            gas_m3: 78
          },
          {
            label: 'Dec 25',
            electricity_kwh: 425,
            water_liters: 36000,
            gas_m3: 95
          },
          {
            label: 'Jan 26',
            electricity_kwh: 459,
            water_liters: 37500,
            gas_m3: 112
          },
          {
            label: 'Feb 26',
            electricity_kwh: 445,
            water_liters: 39100,
            gas_m3: 89
          },
          {
            label: 'Mar 26',
            electricity_kwh: 468,
            water_liters: 41000,
            gas_m3: 74
          },
          {
            label: 'Apr 26',
            electricity_kwh: 412,
            water_liters: 38400,
            gas_m3: 68
          },
        ];
        renderChart(fallback);
      } catch (e) {
        console.warn('Fallback chart failed:', e);
      }
    }

    function renderTableFallback() {
      const fallback = [{
          label: 'Apr 2026',
          electricity_kwh: 412,
          water_liters: 3840,
          gas_m3: 68,
          total_cost: 84,
          co2_kg: 182,
          budget_pct: 65
        },
        {
          label: 'Mar 2026',
          electricity_kwh: 468,
          water_liters: 4100,
          gas_m3: 74,
          total_cost: 95,
          co2_kg: 207,
          budget_pct: 75
        },
        {
          label: 'Feb 2026',
          electricity_kwh: 445,
          water_liters: 3910,
          gas_m3: 89,
          total_cost: 90,
          co2_kg: 198,
          budget_pct: 62
        },
        {
          label: 'Jan 2026',
          electricity_kwh: 459,
          water_liters: 3750,
          gas_m3: 112,
          total_cost: 93,
          co2_kg: 204,
          budget_pct: 60
        },
        {
          label: 'Dec 2025',
          electricity_kwh: 425,
          water_liters: 3600,
          gas_m3: 95,
          total_cost: 86,
          co2_kg: 189,
          budget_pct: 58
        },
        {
          label: 'Nov 2025',
          electricity_kwh: 434,
          water_liters: 3680,
          gas_m3: 78,
          total_cost: 88,
          co2_kg: 194,
          budget_pct: 55
        },
      ];
      renderTable(fallback);
    }

    function renderAuditFallback() {
      const rows = [{
          created_at: new Date().toISOString(),
          user_name: 'Home Owner',
          action: 'alert',
          description: 'Washing Machine overload detected — auto-logged',
          ip_address: '—'
        },
        {
          created_at: new Date().toISOString(),
          user_name: 'Home Owner',
          action: 'setting',
          description: 'Water budget adjusted: $25 → $30',
          ip_address: '192.168.1.5'
        },
        {
          created_at: new Date(Date.now() - 86400000).toISOString(),
          user_name: 'Automation',
          action: 'appliance',
          description: 'EV Charger turned ON by rule "EV Night Charge"',
          ip_address: '—'
        },
      ];
      renderAuditTable(rows);
    }

    /* ─────────────────────────────────────────────────────────
     *  Boot
     * ───────────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', () => {

      /* Logout */
      document.getElementById('logout-btn')?.addEventListener('click', () => {
        if (confirm('Sign out of AOT Homes?')) Auth.logout();
      });

      /* Set default date range → last 6 months */
      const today = new Date();
      const sixMonthsAgo = new Date(today);
      sixMonthsAgo.setMonth(sixMonthsAgo.getMonth() - 6);
      document.getElementById('date-from').value = toYMD(sixMonthsAgo);
      document.getElementById('date-to').value = toYMD(today);

      /* Preset buttons */
      document.querySelectorAll('[data-preset]').forEach(btn => {
        btn.addEventListener('click', () => applyPreset(btn.dataset.preset));
      });

      /* Apply filter button */
      document.getElementById('apply-filter-btn')?.addEventListener('click', () => {
        const from = document.getElementById('date-from').value;
        const to = document.getElementById('date-to').value;
        if (!from || !to) {
          showToast('Please select a date range', 'warn');
          return;
        }
        loadReport(from, to);
      });

      /* Audit type filter */
      document.getElementById('audit-type-filter')?.addEventListener('change', e => {
        loadAuditTrail(e.target.value);
      });

      /* Initial load */
      loadReport(toYMD(sixMonthsAgo), toYMD(today), 'monthly');
      loadAuditTrail();
    });
  </script>
</body>

</html>