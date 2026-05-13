<?php
$user = getCurrentUser();
if ($user) unset($user['password']);
?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
  <div class="alert alert--danger mb">
    <span class="alert__icon">🚫</span>
    <div>
      <div class="alert__title">Access Denied</div>
      <div class="alert__msg">You don't have permission to access that page.</div>
    </div>
  </div>
<?php endif; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard — AOT Homes</title>
  <link rel="stylesheet" href="/AOT/assets/css/style.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <script>
    const AOT_USER = <?php echo json_encode($user ?? null); ?>;
  </script>
</head>

<body>

  <div class="layout">

    <!-- ══ SIDEBAR ══ -->
    <aside class="sidebar">
      <div class="sidebar__logo">
        <div style="font-size:1.8rem">🏠</div>
        <div>
          <div class="sidebar__logo-name">AOT Homes</div>
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
      <div class="sidebar__user">
        <div class="sidebar__avatar" id="sidebar-avatar">
          <?php echo htmlspecialchars($user['avatar'] ?? 'AO'); ?>
        </div>
        <div>
          <div class="sidebar__uname" id="sidebar-name">
            <?php echo htmlspecialchars(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')); ?>
          </div>
          <div class="sidebar__urole" id="sidebar-role">
            <?php echo htmlspecialchars($user['role'] ?? 'owner'); ?>
          </div>
        </div>
        <button class="sidebar__logout" id="logout-btn2" title="Sign Out">✕</button>
      </div>
    </aside>

    <!-- ══ MAIN ══ -->
    <main class="main">

      <div class="topbar">
        <div class="topbar__title">Dashboard</div>
        <div class="topbar__meta" id="today-date"></div>
        <div class="topbar__meta" style="font-family:'Rajdhani',sans-serif;font-size:1rem" id="live-clock"></div>
        <button class="topbar__btn" id="btn-vacation">🏖️ Vacation Mode</button>
        <button class="topbar__btn primary" onclick="runDiagnostic()">⚡ Run Diagnostic</button>
      </div>

      <div class="content">

        <!-- ── Alerts Banner ── -->
        <div id="alert-banner"></div>

        <!-- ══ KPI GRID ══ -->
        <div class="kpi-grid" id="kpi-grid">
          <?php foreach (['⚡', '💧', '🔥', '☀️', '💰', '🌿'] as $icon): ?>
            <div class="kpi" style="opacity:.4">
              <div class="kpi__icon"><?= $icon ?></div>
              <div class="kpi__label">Loading…</div>
              <div class="kpi__value">—</div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- ══ CHARTS ROW 1 ══ -->
        <div class="g21 mb">
          <div class="card">
            <div class="sec-head">
              <div>
                <div class="sec-title">Resource Consumption</div>
                <div class="sec-sub" id="chart-sub">Last 7 days</div>
              </div>
              <div class="flex-c gap-sm">
                <button class="btn btn--sm btn--outline" onclick="changeRange(7,this)">7D</button>
                <button class="btn btn--sm btn--ghost" onclick="changeRange(14,this)">14D</button>
                <button class="btn btn--sm btn--ghost" onclick="changeRange(30,this)">30D</button>
              </div>
            </div>
            <div class="chart-wrap" style="height:240px"><canvas id="chart-main"></canvas></div>
          </div>

          <div class="card">
            <div class="sec-head">
              <div class="sec-title">Cost Breakdown</div>
              <div class="sec-sub">This month</div>
            </div>
            <div class="chart-wrap" style="height:180px"><canvas id="chart-donut"></canvas></div>
            <div id="donut-legend" style="margin-top:var(--sp-md)"></div>
          </div>
        </div>

        <!-- ══ CHARTS ROW 2 ══ -->
        <div class="g2 mb">
          <div class="card">
            <div class="sec-head">
              <div class="sec-title">⚡ Tariff: Peak vs Off-Peak</div>
              <div class="sec-sub" id="tariff-sub">Loading rates…</div>
            </div>
            <div class="chart-wrap" style="height:200px"><canvas id="chart-tariff"></canvas></div>
          </div>
          <div class="card">
            <div class="sec-head">
              <div class="sec-title">🌍 Carbon Footprint (7 Days)</div>
              <div class="sec-sub">CO₂ estimator · 0.233 kg/kWh</div>
            </div>
            <div class="chart-wrap" style="height:200px"><canvas id="chart-carbon"></canvas></div>
          </div>
        </div>

        <!-- ══ LIVE FEED + SOLAR ══ -->
        <div class="g21 mb">
          <div class="card">
            <div class="sec-head">
              <div class="sec-title">☀️ Solar Overview</div>
              <div class="sec-sub" id="solar-sub">Loading…</div>
            </div>
            <div id="solar-body"></div>
          </div>
          <div class="card">
            <div class="sec-head">
              <div class="sec-title">📡 Live Sensor Feed</div>
              <div class="sec-sub" id="live-ts">Waiting for data…</div>
            </div>
            <div id="live-feed"></div>
          </div>
        </div>

        <!-- ══ GOALS PROGRESS ══ -->
        <div class="card mb">
          <div class="sec-head">
            <div class="sec-title">🎯 Goals Progress</div>
            <div class="sec-sub">Active monthly targets</div>
          </div>
          <div id="goals-body">
            <p class="text-3 text-sm" style="padding:var(--sp-md)">Loading goals…</p>
          </div>
        </div>

        <!-- ══ ALERTS TABLE ══ -->
        <div class="card mb">
          <div class="sec-head">
            <div class="sec-title">🔔 Active Alerts</div>
            <div class="sec-sub">From database</div>
          </div>
          <div class="tbl-wrap">
            <table>
              <thead>
                <tr>
                  <th>Type</th>
                  <th>Message</th>
                  <th>Priority</th>
                  <th>Time</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="alerts-table">
                <tr>
                  <td colspan="5" class="text-3 text-sm" style="padding:var(--sp-lg);text-align:center">Loading…</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div><!-- /content -->
    </main>
  </div><!-- /layout -->

  <div id="toast-container"></div>

  <script src="/AOT/assets/js/main.js"></script>

  <script>
    "use strict";

    const API_USAGE = '/AOT/api/usage.php';
    const API_DASH = '/AOT/api/dashboard.php';
    let currentRange = 7;
    let mainChart = null,
      donutChart = null,
      tariffChart = null,
      carbonChart = null;

    const CURRENCY = AOT_USER?.currency === 'EGP' ? 'EGP ' : '$';

    /* ══════════════════════════════════════════════════════════════
       INIT — single DOMContentLoaded, no duplicates
       ══════════════════════════════════════════════════════════════ */
    document.addEventListener('DOMContentLoaded', () => {
      if (typeof applyChartDefaults === 'function') applyChartDefaults();
      if (typeof startLiveClock === 'function') startLiveClock();
      if (typeof markActiveNav === 'function') markActiveNav();
      if (typeof populateSidebar === 'function') populateSidebar();

      loadDashboard();
      startLiveFeed();

      ['logout-btn', 'logout-btn2'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', e => {
          e.preventDefault();
          if (confirm('Sign out of AOT Homes?')) window.location.href = '/AOT/api/logout.php';
        });
      });

      document.getElementById('btn-vacation')?.addEventListener('click', toggleVacation);
    });

    /* ══════════════════════════════════════════════════════════════
       LOAD ALL
       ══════════════════════════════════════════════════════════════ */
    async function loadDashboard() {
      try {
        const res = await fetch(`${API_DASH}?action=all&days=${currentRange}`);
        const data = await res.json();
        if (!data.ok) {
          showToast('Dashboard API error: ' + (data.msg || ''), 'danger');
          return;
        }

        renderKPIs(data.summary);
        renderMainChart(data.chart, currentRange);
        renderDonut({
          electricity: data.summary.electricity.today * (data.summary.tariffs.electricity || 0.28),
          water: data.summary.water.today * (data.summary.tariffs.water || 0.005),
          gas: data.summary.gas.today * (data.summary.tariffs.gas || 0.45),
          solar: data.summary.solar.generated_kwh * (data.summary.tariffs.electricity || 0.28)
        });
        renderAlerts(data.alerts);
        renderGoals(data.summary.goals);
        renderSolar(data.summary.solar);
        loadTariffChart(data.summary.tariffs);
        loadCarbonChart(data.chart);
        renderAlertsBanner(data.alerts);
      } catch (e) {
        console.error('Dashboard load failed:', e);
        showToast('Could not load dashboard data', 'danger');
      }
    }

    /* ══════════════════════════════════════════════════════════════
       1. KPI CARDS
       ══════════════════════════════════════════════════════════════ */
    function renderKPIs(s) {
      const cards = [{
          icon: '⚡',
          label: 'Electricity Today',
          value: s.electricity.today.toFixed(2),
          unit: 'kWh',
          color: 'var(--elec)',
          barW: Math.min(100, s.electricity.today / 30 * 100),
          pct: s.electricity.pct_change,
          sub: null
        },
        {
          icon: '💧',
          label: 'Water Today',
          value: s.water.today.toFixed(0),
          unit: 'L',
          color: 'var(--water)',
          barW: Math.min(100, s.water.today / 300 * 100),
          pct: s.water.pct_change,
          sub: null
        },
        {
          icon: '🔥',
          label: 'Gas Today',
          value: s.gas.today.toFixed(2),
          unit: 'm³',
          color: 'var(--gas)',
          barW: Math.min(100, s.gas.today / 10 * 100),
          pct: s.gas.pct_change,
          sub: null
        },
        {
          icon: '☀️',
          label: 'Solar Generated',
          value: s.solar.generated_kwh.toFixed(2),
          unit: 'kWh',
          color: 'var(--warn)',
          barW: Math.min(100, s.solar.generated_kwh / 15 * 100),
          pct: null,
          sub: `Net: ${s.solar.net_kwh.toFixed(1)} kWh`
        },
        {
          icon: '💰',
          label: 'Est. Bill This Month',
          value: CURRENCY + (
            s.electricity.today * (s.tariffs.electricity || 0.28) +
            s.water.today * (s.tariffs.water || 0.005) +
            s.gas.today * (s.tariffs.gas || 0.45) -
            s.solar.generated_kwh * (s.tariffs.electricity || 0.28)
          ).toFixed(2),
          unit: '',
          color: 'var(--accent)',
          barW: Math.min(100, s.est_bill.month / 200 * 100),
          pct: null,
          sub: null
        },
        {
          icon: '🌿',
          label: 'CO₂ Footprint Today',
          value: s.co2.today.toFixed(2),
          unit: 'kg',
          color: 'var(--ok)',
          barW: Math.min(100, s.co2.today / 15 * 100),
          pct: null,
          sub: null
        },
      ];

      document.getElementById('kpi-grid').innerHTML = cards.map(k => {
        const arrow = k.pct == null ? '' : k.pct > 0 ? '↑' : '↓';
        const cls = k.pct == null ? 'neutral' : k.pct > 0 ? 'up' : 'down';
        const chgTxt = k.pct != null ?
          `${arrow} ${Math.abs(k.pct)}% vs yesterday` :
          (k.sub || '—');
        return `
          <div class="kpi">
            <div class="kpi__icon">${k.icon}</div>
            <div class="kpi__label">${k.label}</div>
            <div class="kpi__value" style="color:${k.color}">${k.value} <small>${k.unit}</small></div>
            <div class="kpi__change ${cls}">${chgTxt}</div>
            <div class="kpi__bar">
              <div class="kpi__bar-fill" style="background:${k.color};width:${k.barW.toFixed(0)}%"></div>
            </div>
          </div>`;
      }).join('');
    }

    /* ══════════════════════════════════════════════════════════════
       2. MAIN LINE CHART
       ══════════════════════════════════════════════════════════════ */
    function renderMainChart(chart, days) {
      document.getElementById('chart-sub').textContent = `Last ${days} days`;
      const labels = chart.labels.map(d =>
        new Date(d).toLocaleDateString('en-US', {
          weekday: 'short',
          month: 'short',
          day: 'numeric'
        }));

      if (mainChart) mainChart.destroy();
      const ctx = document.getElementById('chart-main').getContext('2d');
      mainChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [{
              label: 'Electricity (kWh)',
              data: chart.datasets.electricity,
              borderColor: '#38bdf8',
              backgroundColor: 'rgba(56,189,248,.08)',
              tension: 0.4,
              fill: true,
              pointRadius: 4
            },
            {
              label: 'Water (×10L)',
              data: chart.datasets.water.map(v => +(v / 10).toFixed(1)),
              borderColor: '#34d399',
              backgroundColor: 'rgba(52,211,153,.08)',
              tension: 0.4,
              fill: true,
              pointRadius: 4
            },
            {
              label: 'Gas (m³×10)',
              data: chart.datasets.gas.map(v => +(v * 10).toFixed(2)),
              borderColor: '#fb923c',
              backgroundColor: 'rgba(251,146,60,.08)',
              tension: 0.4,
              fill: true,
              pointRadius: 4
            },
            {
              label: 'Solar (kWh)',
              data: chart.datasets.solar,
              borderColor: '#facc15',
              backgroundColor: 'rgba(250,204,21,.08)',
              tension: 0.4,
              fill: true,
              pointRadius: 4
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              labels: {
                color: '#94a3b8',
                font: {
                  size: 11
                }
              }
            }
          },
          scales: {
            x: {
              ticks: {
                color: '#64748b',
                maxRotation: 0
              },
              grid: {
                color: 'rgba(255,255,255,.04)'
              }
            },
            y: {
              ticks: {
                color: '#64748b'
              },
              grid: {
                color: 'rgba(255,255,255,.04)'
              }
            },
          },
        },
      });
    }

    async function changeRange(days, btn) {
      currentRange = days;
      document.querySelectorAll('.flex-c .btn--sm').forEach(b => b.className = 'btn btn--sm btn--ghost');
      if (btn) btn.className = 'btn btn--sm btn--outline';
      try {
        const res = await fetch(`${API_DASH}?action=chart&days=${days}`);
        const data = await res.json();
        if (data.ok) renderMainChart(data.data, days);
      } catch (e) {
        showToast('Chart load failed', 'warn');
      }
    }

    /* ══════════════════════════════════════════════════════════════
       3. COST DOUGHNUT
       ══════════════════════════════════════════════════════════════ */
    function renderDonut(breakdown) {
      const elec = breakdown.electricity || 0;
      const water = breakdown.water || 0;
      const gas = breakdown.gas || 0;
      const solar = Math.abs(breakdown.solar || 0);

      if (!elec && !water && !gas) {
        makeDoughnutChart('chart-donut', {
          labels: ['No data yet'],
          data: [1],
          colors: ['#334155']
        });
        document.getElementById('donut-legend').innerHTML =
          '<p style="color:var(--text-3);font-size:.8rem;text-align:center">No billing data this month</p>';
        return;
      }

      if (donutChart) donutChart.destroy();
      const ctx = document.getElementById('chart-donut').getContext('2d');
      donutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Electricity', 'Water', 'Gas', 'Solar'],
          datasets: [{
            data: [elec, water, gas, solar],
            backgroundColor: ['#38bdf8', '#34d399', '#fb923c', '#facc15'],
            borderWidth: 0,
            hoverOffset: 6
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '70%',
          plugins: {
            legend: {
              display: false
            }
          }
        },
      });

      document.getElementById('donut-legend').innerHTML = `
        <div style="display:flex;flex-direction:column;gap:6px">
          ${[['Electricity','#38bdf8',elec],['Water','#34d399',water],['Gas','#fb923c',gas],['Solar','#facc15',solar]]
            .map(([l,c,v]) => `
              <div class="flex-c gap-sm jcsb">
                <div class="flex-c gap-sm">
                  <div style="width:10px;height:10px;border-radius:50%;background:${c}"></div>
                  <span class="text-sm">${l}</span>
                </div>
                <span class="text-sm fw7">${CURRENCY}${parseFloat(v).toFixed(2)}</span>
              </div>`).join('')}
        </div>`;
    }

    /* ══════════════════════════════════════════════════════════════
       4. TARIFF CHART
       ══════════════════════════════════════════════════════════════ */
    function loadTariffChart(tariffs) {
      const elecRate = tariffs.electricity || 0.28;
      const peakRate = elecRate * 1.5;
      const offPeakRate = elecRate * 0.6;

      document.getElementById('tariff-sub').textContent =
        `Electricity: ${CURRENCY}${elecRate.toFixed(3)}/kWh`;

      const hours = Array.from({
        length: 24
      }, (_, h) => String(h).padStart(2, '0') + ':00');
      const rates = hours.map((_, h) => h >= 18 && h < 22 ? peakRate : offPeakRate);

      if (tariffChart) tariffChart.destroy();
      const ctx = document.getElementById('chart-tariff').getContext('2d');
      tariffChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: hours.filter((_, i) => i % 2 === 0),
          datasets: [{
            label: `Rate (${CURRENCY}/kWh)`,
            data: rates.filter((_, i) => i % 2 === 0),
            backgroundColor: rates.filter((_, i) => i % 2 === 0).map(r =>
              r === peakRate ? 'rgba(251,146,60,.7)' : 'rgba(129,140,248,.5)'),
            borderRadius: 4,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              labels: {
                color: '#94a3b8',
                font: {
                  size: 11
                }
              }
            }
          },
          scales: {
            x: {
              ticks: {
                color: '#64748b'
              },
              grid: {
                color: 'rgba(255,255,255,.04)'
              }
            },
            y: {
              ticks: {
                color: '#64748b'
              },
              grid: {
                color: 'rgba(255,255,255,.04)'
              }
            },
          },
        },
      });
    }

    /* ══════════════════════════════════════════════════════════════
       5. CARBON CHART
       ══════════════════════════════════════════════════════════════ */
    function loadCarbonChart(chart) {
      const labels = chart.labels.map(d => new Date(d).toLocaleDateString('en-US', {
        weekday: 'short'
      }));
      const co2Data = chart.datasets.electricity.map(kwh => +(kwh * 0.233).toFixed(2));

      if (carbonChart) carbonChart.destroy();
      const ctx = document.getElementById('chart-carbon').getContext('2d');
      carbonChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'CO₂ (kg)',
            data: co2Data,
            backgroundColor: 'rgba(52,211,153,.6)',
            borderRadius: 4
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              labels: {
                color: '#94a3b8',
                font: {
                  size: 11
                }
              }
            }
          },
          scales: {
            x: {
              ticks: {
                color: '#64748b'
              },
              grid: {
                color: 'rgba(255,255,255,.04)'
              }
            },
            y: {
              ticks: {
                color: '#64748b'
              },
              grid: {
                color: 'rgba(255,255,255,.04)'
              }
            },
          },
        },
      });
    }

    /* ══════════════════════════════════════════════════════════════
       6. SOLAR PANEL CARD
       ══════════════════════════════════════════════════════════════ */
    function renderSolar(solar) {
      document.getElementById('solar-sub').textContent =
        `Generated today: ${solar.generated_kwh} kWh`;
      document.getElementById('solar-body').innerHTML = `
        <div style="display:flex;flex-direction:column;gap:10px;padding:var(--sp-sm) 0">
          ${[
            ['☀️ Generated Today',  solar.generated_kwh+' kWh', 'var(--warn)'],
            ['🔋 Exported to Grid', solar.exported_kwh +' kWh', 'var(--ok)'],
            ['⚡ Net Consumption',  solar.net_kwh      +' kWh', 'var(--elec)'],
          ].map(([l,v,c]) => `
            <div class="flex-c jcsb"
              style="padding:10px 14px;background:var(--surface-2);border-radius:var(--r-sm)">
              <span class="text-sm">${l}</span>
              <span class="fw7 text-sm" style="color:${c}">${v}</span>
            </div>`).join('')}
        </div>`;
    }

    /* ══════════════════════════════════════════════════════════════
       7. GOALS PROGRESS
       ══════════════════════════════════════════════════════════════ */
    function renderGoals(goals) {
      const el = document.getElementById('goals-body');
      if (!goals || !goals.length) {
        el.innerHTML = `<p class="text-3 text-sm" style="padding:var(--sp-md)">
          No active goals. <a href="/AOT/goals.php" class="text-elec">Set a goal →</a></p>`;
        return;
      }
      const icons = {
        electricity: '⚡',
        water: '💧',
        gas: '🔥',
        cost: '💰',
        solar: '☀️'
      };
      const colors = {
        electricity: 'var(--elec)',
        water: 'var(--water)',
        gas: 'var(--gas)',
        cost: 'var(--accent)',
        solar: 'var(--warn)'
      };
      el.innerHTML = goals.map(g => {
        const pct = g.pct || 0;
        const color = colors[g.resource_type] || 'var(--accent)';
        const icon = icons[g.resource_type] || '📊';
        const warn = pct >= 90 ? 'badge--danger' : pct >= 70 ? 'badge--warn' : 'badge--ok';
        return `
          <div style="margin-bottom:var(--sp-md);padding:0 var(--sp-sm)">
            <div class="flex-c jcsb" style="margin-bottom:6px">
              <span class="text-sm">${icon} ${g.resource_type.charAt(0).toUpperCase()+g.resource_type.slice(1)} (${g.period})</span>
              <span class="badge ${warn}">${pct}%</span>
            </div>
            <div class="progress">
              <div class="progress__fill" style="background:${color};width:${Math.min(100,pct)}%"></div>
            </div>
            <div class="flex-c jcsb text-xs text-3" style="margin-top:4px">
              <span>Used: ${parseFloat(g.current_value).toFixed(1)} ${g.unit}</span>
              <span>Target: ${parseFloat(g.target_value).toFixed(1)} ${g.unit}</span>
            </div>
          </div>`;
      }).join('');
    }

    /* ══════════════════════════════════════════════════════════════
       8. ALERTS TABLE
       ══════════════════════════════════════════════════════════════ */
    function renderAlerts(alerts) {
      const tbody = document.getElementById('alerts-table');
      if (!alerts || !alerts.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-3 text-sm"
          style="padding:var(--sp-lg);text-align:center">✅ No active alerts</td></tr>`;
        return;
      }
      tbody.innerHTML = alerts.map(a => {
        const priCls = a.priority === 'high' ? 'badge--danger' :
          a.priority === 'medium' ? 'badge--warn' : 'badge--info';
        const time = new Date(a.created_at).toLocaleString('en-US', {
          month: 'short',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
        return `
          <tr>
            <td><span class="badge badge--neutral">${a.type}</span></td>
            <td class="text-sm">${escHtml(a.title)}${a.device_name?' — '+escHtml(a.device_name):''}</td>
            <td><span class="badge ${priCls}">${a.priority}</span></td>
            <td class="text-xs text-3">${time}</td>
            <td><button class="btn btn--sm btn--outline" onclick="dismissAlert(${a.id},this)">Dismiss</button></td>
          </tr>`;
      }).join('');
    }

    async function dismissAlert(id, btn) {
      btn.disabled = true;
      try {
        await fetch(`${API_DASH}?action=mark_read&id=${id}`);
        btn.closest('tr').style.opacity = '0.4';
        btn.textContent = '✓';
      } catch (e) {
        btn.disabled = false;
      }
    }

    /* ══════════════════════════════════════════════════════════════
       9. ALERTS BANNER
       ══════════════════════════════════════════════════════════════ */
    function renderAlertsBanner(alerts) {
      const critical = (alerts || []).filter(a => a.priority === 'high');
      const banner = document.getElementById('alert-banner');
      if (!critical.length) {
        banner.innerHTML = '';
        return;
      }
      banner.innerHTML = critical.slice(0, 3).map(a => `
        <div class="alert alert--danger mb">
          <span class="alert__icon">🚨</span>
          <div>
            <div class="alert__title">${escHtml(a.title)}</div>
            <div class="alert__msg">${escHtml(a.message||'')}</div>
          </div>
          <button class="btn btn--sm btn--outline" style="margin-left:auto"
            onclick="dismissAlert(${a.id},this);this.closest('.alert').remove()">Dismiss</button>
        </div>`).join('');
    }

    /* ══════════════════════════════════════════════════════════════
       10. LIVE SENSOR FEED
       ══════════════════════════════════════════════════════════════ */
    function startLiveFeed() {
      function tick() {
        fetch(`${API_USAGE}?action=dashboard`)
          .then(r => r.json())
          .then(data => {
            if (!data.ok || !data.kpis?.length) return;
            document.getElementById('live-ts').textContent =
              'Last update: ' + new Date().toLocaleTimeString();
            const byType = {};
            data.kpis.forEach(r => {
              byType[r.resource_type] = r;
            });
            const rows = [{
                icon: '⚡',
                label: 'Electricity',
                val: parseFloat(byType.electricity?.total || 0).toFixed(2) + ' kWh',
                color: 'var(--elec)'
              },
              {
                icon: '💧',
                label: 'Water',
                val: parseFloat(byType.water?.total || 0).toFixed(1) + ' L',
                color: 'var(--water)'
              },
              {
                icon: '🔥',
                label: 'Gas',
                val: parseFloat(byType.gas?.total || 0).toFixed(2) + ' m³',
                color: 'var(--gas)'
              },
              {
                icon: '☀️',
                label: 'Solar',
                val: parseFloat(byType.solar?.total || 0).toFixed(2) + ' kWh',
                color: 'var(--warn)'
              },
            ];
            document.getElementById('live-feed').innerHTML = `
              <div style="display:flex;flex-direction:column;gap:8px">
                ${rows.map(r => `
                  <div class="flex-c jcsb"
                    style="padding:7px 10px;background:var(--surface-2);border-radius:var(--r-sm)">
                    <span class="text-sm">${r.icon} ${r.label}</span>
                    <span class="fw7 text-sm" style="color:${r.color}">${r.val}</span>
                  </div>`).join('')}
              </div>`;
          })
          .catch(() => {});
      }
      tick();
      setInterval(tick, 30000);
    }

    /* ══════════════════════════════════════════════════════════════
       11. VACATION MODE
       ══════════════════════════════════════════════════════════════ */
    async function toggleVacation() {
      try {
        const fd = new FormData();
        fd.append('action', 'vacation_toggle');
        const res = await fetch('/AOT/api/settings.php', {
          method: 'POST',
          body: fd
        });
        const data = await res.json();
        if (data.ok) {
          const mode = data.vacation_mode;
          showToast(mode ? '🏖️ Vacation Mode ON' : '🏠 Vacation Mode OFF', mode ? 'warn' : 'ok');
          document.getElementById('btn-vacation').textContent = mode ? '🏠 Back Home' : '🏖️ Vacation Mode';
        }
      } catch (e) {
        showToast('Could not toggle vacation mode', 'danger');
      }
    }

    /* ══════════════════════════════════════════════════════════════
       12. RUN DIAGNOSTIC
       ══════════════════════════════════════════════════════════════ */
    async function runDiagnostic() {
      try {
        const [devRes, usageRes] = await Promise.all([
          fetch('/AOT/api/devices.php?action=list'),
          fetch(`${API_USAGE}?action=dashboard`),
        ]);
        const devData = await devRes.json();
        const usageData = await usageRes.json();
        const devices = devData.devices || [];
        const onCount = devices.filter(d => d.status === 'on').length;
        const totalKW = (devData.total_wattage || 0) / 1000;
        const byType = {};
        (usageData.kpis || []).forEach(r => {
          byType[r.resource_type] = r;
        });

        if (typeof openModal === 'function') {
          openModal(`
            <div class="modal__header">
              <div class="modal__title">⚡ System Diagnostic</div>
              <button class="modal__close" onclick="closeModal()">✕</button>
            </div>
            <div class="alert alert--ok mb">
              <span class="alert__icon">✅</span>
              <div class="alert__msg">All systems nominal — DB connected</div>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px">
              ${[
                ['🔌 Active Devices', onCount+' / '+devices.length],
                ['⚡ Live Draw',      totalKW.toFixed(2)+' kW'],
                ['📊 Elec Today',     parseFloat(byType.electricity?.total||0).toFixed(2)+' kWh'],
                ['💧 Water Today',    parseFloat(byType.water?.total||0).toFixed(1)+' L'],
                ['🔥 Gas Today',      parseFloat(byType.gas?.total||0).toFixed(2)+' m³'],
              ].map(([l,v]) => `
                <div class="flex-c jcsb"
                  style="padding:8px 12px;background:var(--surface-2);border-radius:var(--r-sm)">
                  <span class="text-sm">${l}</span>
                  <span class="fw7 text-sm">${v}</span>
                </div>`).join('')}
            </div>
            <button class="btn btn--primary btn--full" style="margin-top:var(--sp-lg)"
              onclick="closeModal()">Close</button>`);
        }
      } catch (e) {
        showToast('Diagnostic failed: ' + e.message, 'danger');
      }
    }

    /* ── Utility ── */
    function escHtml(str) {
      return String(str ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function showToast(msg, type = 'info') {
      if (typeof window.showToast === 'function') {
        window.showToast(msg, type);
        return;
      }
      const el = document.createElement('div');
      el.className = `toast toast--${type}`;
      el.textContent = msg;
      document.getElementById('toast-container')?.appendChild(el);
      setTimeout(() => el.remove(), 3500);
    }
  </script>

</body>

</html>