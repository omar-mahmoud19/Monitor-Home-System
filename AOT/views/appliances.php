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
  <title>Appliances — AOT Homes</title>
  <link rel="stylesheet" href="/AOT/assets/css/style.css" />
  <script src="/AOT/assets/js/main.js" defer></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <script>
    const AOT_USER = <?php echo json_encode($user ?? null); ?>;
  </script>
</head>

<body>

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
      <div class="sidebar__avatar" id="sidebar-avatar"><?php echo htmlspecialchars($user['avatar'] ?? 'AO'); ?></div>
      <div>
        <div class="sidebar__user-name" id="sidebar-name"><?php echo htmlspecialchars(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')); ?></div>
        <div class="sidebar__user-role" id="sidebar-role"><?php echo htmlspecialchars($user['role'] ?? 'owner'); ?></div>
      </div>
      <button class="sidebar__logout" id="logout-btn" title="Sign Out">✕</button>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <h1 class="topbar__title">Appliances &amp; Devices</h1>
      <span class="topbar__meta" id="today-date"></span>
      <span class="topbar__meta" id="live-clock"></span>
      <button class="topbar__btn primary" onclick="openAddModal()">+ Add Device</button>
    </header>

    <div class="content">
      <div id="alert-banner"></div>

      <!-- KPI Row -->
      <div class="kpi-grid" style="grid-template-columns:repeat(4,1fr)" id="kpi-row">
        <div class="kpi-card"><span class="kpi-card__icon">🔌</span>
          <div class="kpi-card__label">Total Devices</div>
          <div class="kpi-card__value" id="kpi-total">—</div>
          <div class="kpi-card__change" id="kpi-total-sub">Loading…</div>
        </div>
        <div class="kpi-card"><span class="kpi-card__icon">⚡</span>
          <div class="kpi-card__label">Live Draw</div>
          <div class="kpi-card__value" style="color:var(--clr-elec)" id="kpi-draw">—</div>
          <div class="kpi-card__change" id="kpi-draw-sub">Loading…</div>
        </div>
        <div class="kpi-card"><span class="kpi-card__icon">🧛</span>
          <div class="kpi-card__label">Vampire Power</div>
          <div class="kpi-card__value" style="color:var(--clr-warn)" id="kpi-vampire">—</div>
          <div class="kpi-card__change" id="kpi-vampire-sub">standby devices</div>
        </div>
        <div class="kpi-card"><span class="kpi-card__icon">🛡️</span>
          <div class="kpi-card__label">Health Alerts</div>
          <div class="kpi-card__value" style="color:var(--clr-danger)" id="kpi-health">—</div>
          <div class="kpi-card__change" id="kpi-health-sub">Loading…</div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid-2 mb-lg">
        <div class="card">
          <div class="section-header">
            <div class="section-title">Per-Device Usage Today (kWh)</div>
          </div>
          <div class="chart-wrap" style="height:240px"><canvas id="chart-appliance-bar"></canvas></div>
        </div>
        <div class="card">
          <div class="section-header">
            <div class="section-title">Total Consumption by Resource</div>
          </div>
          <div class="chart-wrap" style="height:240px"><canvas id="chart-vampire"></canvas></div>
        </div>
      </div>

      <!-- Device Table -->
      <div class="card">
        <div class="section-header">
          <div>
            <div class="section-title">Device Inventory</div>
            <div class="section-sub" id="table-sub">Loading from database…</div>
          </div>
          <div class="flex-center gap-sm">
            <select class="form-select" id="filter-location" style="width:auto;padding:6px 28px 6px 10px;font-size:.78rem" onchange="applyFilters()">
              <option value="">All Rooms</option>
            </select>
            <select class="form-select" id="filter-status" style="width:auto;padding:6px 28px 6px 10px;font-size:.78rem" onchange="applyFilters()">
              <option value="">All Status</option>
              <option value="on">Active</option>
              <option value="off">Off</option>
              <option value="standby">Standby</option>
            </select>
          </div>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Device</th>
                <th>Location</th>
                <th>Status</th>
                <th>Resources</th>
                <th>Health</th>
                <th>Control</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="devices-tbody">
              <tr>
                <td colspan="8" style="padding:var(--sp-xl);text-align:center;color:var(--text-3)">Loading devices…</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- ══ ADD DEVICE MODAL ══ -->
  <div id="modal-add" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:200;align-items:center;justify-content:center;backdrop-filter:blur(4px)">
    <div class="card" style="width:500px;max-width:92vw;position:relative;background:var(--surface-2);border-color:var(--border-2)">
      <div class="section-header">
        <div class="section-title">Add New Device</div>
        <button class="btn btn--outline btn--sm" onclick="closeAddModal()">✕ Close</button>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Device Name *</label>
          <input class="form-input" type="text" id="add-name" placeholder="e.g. Kitchen AC" />
          <span class="form-error" id="err-name"></span>
        </div>
        <div class="form-group">
          <label class="form-label">Device Type *</label>
          <select class="form-select" id="add-type" onchange="prefillDefaults()">
            <option value="ac">Air Conditioner</option>
            <option value="refrigerator">Refrigerator</option>
            <option value="washing_machine">Washing Machine</option>
            <option value="water_heater">Water Heater</option>
            <option value="light">Lighting</option>
            <option value="solar_panel">Solar Panel</option>
            <option value="generic">Other / Generic</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Location / Room</label>
          <input class="form-input" type="text" id="add-location" placeholder="e.g. Living Room" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Category</label>
          <input class="form-input" type="text" id="add-category" placeholder="e.g. cooling" />
        </div>
        <div class="form-group">
          <label class="form-label">Icon (emoji)</label>
          <input class="form-input" type="text" id="add-icon" placeholder="e.g. ❄️" maxlength="4" />
        </div>
      </div>
      <div class="form-group" style="margin-top:var(--sp-md)">
        <label class="form-label">Resources Consumed</label>
        <div style="display:flex;flex-direction:column;gap:var(--sp-sm);margin-top:var(--sp-xs)">
          <label style="display:flex;align-items:center;gap:var(--sp-sm);font-size:.82rem">
            <input type="checkbox" id="res-electricity" onchange="toggleRate(this,'electricity')">
            ⚡ Electricity
            <input class="form-input" type="number" id="rate-electricity" placeholder="kWh/h" step="0.01" disabled style="width:100px;margin-left:auto" />
          </label>
          <label style="display:flex;align-items:center;gap:var(--sp-sm);font-size:.82rem">
            <input type="checkbox" id="res-water" onchange="toggleRate(this,'water')">
            💧 Water
            <input class="form-input" type="number" id="rate-water" placeholder="L/h" step="0.1" disabled style="width:100px;margin-left:auto" />
          </label>
          <label style="display:flex;align-items:center;gap:var(--sp-sm);font-size:.82rem">
            <input type="checkbox" id="res-gas" onchange="toggleRate(this,'gas')">
            🔥 Gas
            <input class="form-input" type="number" id="rate-gas" placeholder="m³/h" step="0.01" disabled style="width:100px;margin-left:auto" />
          </label>
          <label style="display:flex;align-items:center;gap:var(--sp-sm);font-size:.82rem">
            <input type="checkbox" id="res-solar" onchange="toggleRate(this,'solar')">
            ☀️ Solar
            <input class="form-input" type="number" id="rate-solar" placeholder="kWh/h" step="0.01" disabled style="width:100px;margin-left:auto" />
          </label>
        </div>
      </div>
      <div style="display:flex;gap:var(--sp-sm);justify-content:flex-end;margin-top:var(--sp-md)">
        <button class="btn btn--outline" onclick="closeAddModal()">Cancel</button>
        <button class="btn btn--primary" id="btn-save-device">
          <span id="save-txt">Save Device</span>
          <span id="save-spin" style="display:none">⏳ Saving…</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ══ EDIT DEVICE MODAL ══ -->
  <div id="modal-edit" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:200;align-items:center;justify-content:center;backdrop-filter:blur(4px)">
    <div class="card" style="width:500px;max-width:92vw;background:var(--surface-2);border-color:var(--border-2)">
      <div class="section-header">
        <div class="section-title">Edit Device</div>
        <button class="btn btn--outline btn--sm" onclick="closeEditModal()">✕ Close</button>
      </div>
      <input type="hidden" id="edit-id" />
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Device Name</label>
          <input class="form-input" type="text" id="edit-name" />
        </div>
        <div class="form-group">
          <label class="form-label">Location</label>
          <input class="form-input" type="text" id="edit-location" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Category</label>
          <input class="form-input" type="text" id="edit-category" />
        </div>
      </div>
      <div class="form-group" style="margin-top:var(--sp-md)">
        <label class="form-label">Resources Consumed</label>
        <div style="display:flex;flex-direction:column;gap:var(--sp-sm);margin-top:var(--sp-xs)">
          <label style="display:flex;align-items:center;gap:var(--sp-sm);font-size:.82rem">
            <input type="checkbox" id="edit-res-electricity" onchange="toggleEditRate(this,'electricity')">
            ⚡ Electricity
            <input class="form-input" type="number" id="edit-rate-electricity" placeholder="kWh/h" step="0.01" disabled style="width:100px;margin-left:auto" />
          </label>
          <label style="display:flex;align-items:center;gap:var(--sp-sm);font-size:.82rem">
            <input type="checkbox" id="edit-res-water" onchange="toggleEditRate(this,'water')">
            💧 Water
            <input class="form-input" type="number" id="edit-rate-water" placeholder="L/h" step="0.1" disabled style="width:100px;margin-left:auto" />
          </label>
          <label style="display:flex;align-items:center;gap:var(--sp-sm);font-size:.82rem">
            <input type="checkbox" id="edit-res-gas" onchange="toggleEditRate(this,'gas')">
            🔥 Gas
            <input class="form-input" type="number" id="edit-rate-gas" placeholder="m³/h" step="0.01" disabled style="width:100px;margin-left:auto" />
          </label>
          <label style="display:flex;align-items:center;gap:var(--sp-sm);font-size:.82rem">
            <input type="checkbox" id="edit-res-solar" onchange="toggleEditRate(this,'solar')">
            ☀️ Solar
            <input class="form-input" type="number" id="edit-rate-solar" placeholder="kWh/h" step="0.01" disabled style="width:100px;margin-left:auto" />
          </label>
        </div>
      </div>
      <div style="display:flex;gap:var(--sp-sm);justify-content:flex-end;margin-top:var(--sp-md)">
        <button class="btn btn--outline" onclick="closeEditModal()">Cancel</button>
        <button class="btn btn--primary" onclick="updateDevice()">Update Device</button>
      </div>
    </div>
  </div>

  <div id="toast-container"></div>

  <script>
    "use strict";

    const API_DEVICES = '/AOT/api/devices.php';

    const TYPE_DEFAULTS = {
      ac: {
        category: 'cooling',
        icon: '❄️',
        location: 'Living Room',
        resources: [{
          type: 'electricity',
          rate: 1.5
        }]
      },
      refrigerator: {
        category: 'kitchen',
        icon: '🧊',
        location: 'Kitchen',
        resources: [{
          type: 'electricity',
          rate: 0.15
        }]
      },
      washing_machine: {
        category: 'laundry',
        icon: '🌀',
        location: 'Laundry Room',
        resources: [{
          type: 'electricity',
          rate: 0.5
        }, {
          type: 'water',
          rate: 60
        }]
      },
      water_heater: {
        category: 'heating',
        icon: '🔥',
        location: 'Bathroom',
        resources: [{
          type: 'electricity',
          rate: 2.0
        }, {
          type: 'water',
          rate: 50
        }]
      },
      light: {
        category: 'lighting',
        icon: '💡',
        location: 'Room',
        resources: [{
          type: 'electricity',
          rate: 0.01
        }]
      },
      solar_panel: {
        category: 'solar',
        icon: '☀️',
        location: 'Roof',
        resources: [{
          type: 'solar',
          rate: 0.4
        }]
      },
      generic: {
        category: 'other',
        icon: '🔌',
        location: 'Home',
        resources: [{
          type: 'electricity',
          rate: 0.1
        }]
      },
    };
    let allDevices = [];

    document.addEventListener('DOMContentLoaded', () => {
      applyChartDefaults();
      startLiveClock();
      markActiveNav();
      populateSidebar();
      RBAC.applyToUI();
      loadDevices();
      document.getElementById('btn-save-device')?.addEventListener('click', saveDevice);
      document.getElementById('logout-btn')?.addEventListener('click', e => {
        e.preventDefault();
        if (confirm('Sign out of AOT Homes?')) window.location.href = '/AOT/api/logout.php';
      });
    });

    /* ── Load Devices ── */
    async function loadDevices() {
      try {
        const res = await fetch(`${API_DEVICES}?action=list`);

        const text = await res.text();
        console.log('API Response:', text);

        const data = JSON.parse(text);
        if (!data.ok) {
          console.error('API not ok:', data);
          loadFallback();
          return;
        }
        allDevices = data.devices || [];
        renderAll(allDevices, parseFloat(data.total_wattage || 0));
      } catch (e) {
        console.error('Fetch error:', e);
        loadFallback();
      }
    }

    function loadFallback() {
      allDevices = ApplianceDB.getAll().map(a => ({
        id: a.id,
        name: a.name,
        type: a.type,
        category: a.type,
        wattage: a.currentWatts || a.nominalWatts,
        status: a.isOn ? 'on' : 'off',
        location: 'Home',
        icon: ApplianceSignatures[a.type]?.icon || '🔌',
        daily_usage_kwh: +((a.currentWatts / 1000) * a.hoursPerDay).toFixed(2),
      }));
      const totalW = allDevices.filter(d => d.status === 'on').reduce((s, d) => s + parseFloat(d.wattage || 0), 0);
      renderAll(allDevices, totalW);
    }

    function renderAll(devices, totalWattage) {
      renderKPIs(devices, totalWattage);
      renderAlertsBanner(devices);
      renderTable(devices);
      populateLocationFilter(devices);
      renderUsageChart(devices);
      renderVampireChart(devices);
    }

    /* ── KPIs ── */
    function renderKPIs(devices, totalWattage) {
      const active = devices.filter(d => d.status === 'on');
      const offDevs = devices.filter(d => d.status !== 'on');
      const vampireW = offDevs.reduce((s, d) => {
        const elec = (d.resources || []).find(r => r.resource_type === 'electricity');
        return s + parseFloat(elec?.consumption_rate || 0) * 0.01;
      }, 0);
      let critical = 0,
        warn = 0;
      devices.forEach(d => {
        const resources = d.resources || [];


        if (d.status === 'on' && !resources.length) {
          warn++;
        }


        const elec = resources.find(r => r.resource_type === 'electricity');
        if (elec && parseFloat(elec.consumption_rate) > 3) {
          if (parseFloat(elec.consumption_rate) > 10) critical++;
          else warn++;
        }


        if (d.type === 'solar_panel' && d.status === 'off') {
          warn++;
        }
      });
      document.getElementById('kpi-total').textContent = devices.length;
      document.getElementById('kpi-total-sub').textContent = `${active.length} active · ${offDevs.length} off`;
      const liveKwh = active.reduce((sum, d) => {
        const elec = (d.resources || []).find(r => r.resource_type === 'electricity');
        return sum + parseFloat(elec?.consumption_rate || 0);
      }, 0);
      document.getElementById('kpi-draw').innerHTML = `${liveKwh.toFixed(2)} <small style="font-size:1rem">kW</small>`;
      document.getElementById('kpi-draw-sub').textContent = `${active.length} devices on`;
      document.getElementById('kpi-vampire').innerHTML = `${vampireW.toFixed(2)} <small style="font-size:1rem">kWh</small>`;
      document.getElementById('kpi-vampire-sub').textContent = `${offDevs.length} standby devices`;
      document.getElementById('kpi-health').textContent = critical + warn;
      document.getElementById('kpi-health-sub').textContent = `${critical} critical · ${warn} warning`;
    }

    /* ── Alerts Banner ── */
    function renderAlertsBanner(devices) {
      const alerts = [];
      devices.forEach(d => {
        const watt = parseFloat(d.wattage || 0);
        const eff = parseFloat(d.efficiency_level || 100);
        const thresh = parseFloat(d.efficiency_threshold || 80);
        if (eff < thresh) alerts.push({
          severity: eff < 70 ? 'high' : 'medium',
          msg: `${d.name} running at ${eff.toFixed(1)}% efficiency. Consider servicing.`
        });
        if (d.status === 'on' && watt > 3000) alerts.push({
          severity: 'high',
          msg: `${d.name} drawing ${watt}W — high power usage detected.`
        });
        if (d.status === 'off' && parseFloat(d.standby_watts || 0) > 10) alerts.push({
          severity: 'medium',
          msg: `Ghost load: ${d.name} drawing ${d.standby_watts}W while OFF.`
        });
      });
      const banner = document.getElementById('alert-banner');
      if (!alerts.length) {
        banner.innerHTML = '';
        return;
      }
      banner.innerHTML = alerts.slice(0, 3).map(a => `
        <div class="alert alert--${a.severity==='high'?'danger':'warn'}" style="margin-bottom:var(--sp-sm)">
          <span class="alert__icon">${a.severity==='high'?'🚨':'⚠️'}</span>
          <div>
            <div class="alert__title">${a.severity==='high'?'Critical Alert':'Warning'}</div>
            <div class="alert__msg">${a.msg}</div>
          </div>
          <button class="btn btn--sm btn--outline" style="margin-left:auto;flex-shrink:0" onclick="this.closest('.alert').remove()">Dismiss</button>
        </div>`).join('');
    }

    /* ── Device Table ── */
    function renderTable(devices) {
      document.getElementById('table-sub').textContent = `${devices.length} device${devices.length !== 1 ? 's' : ''} in database`;
      const tbody = document.getElementById('devices-tbody');
      if (!devices.length) {
        tbody.innerHTML = `<tr><td colspan="8" style="padding:var(--sp-xl);text-align:center;color:var(--text-3)">No devices found. Click <strong>+ Add Device</strong> to get started.</td></tr>`;
        return;
      }
      tbody.innerHTML = devices.map(d => {
        const watt = parseFloat(d.wattage || 0);
        // const dailyKwh = d.daily_usage_kwh ? parseFloat(d.daily_usage_kwh).toFixed(2) : ((watt / 1000) * 8).toFixed(2);
        const statusCls = d.status === 'on' ? 'badge--ok' : d.status === 'standby' ? 'badge--warn' : 'badge--neutral';
        const statusLbl = d.status === 'on' ? 'Active' : d.status === 'standby' ? 'Standby' : 'Off';
        const resources = d.resources || [];
        const elec = resources.find(r => r.resource_type === 'electricity');
        const elecRate = parseFloat(elec?.consumption_rate || 0);
        const isSolar = d.type === 'solar_panel';

        let healthCls, healthLbl;

        if (isSolar && d.status === 'off') {
          healthCls = 'badge--warn';
          healthLbl = '⚠️ Panel Off';
        } else if (isSolar) {
          healthCls = 'badge--ok';
          healthLbl = '☀️ Generating';
        } else if (d.status === 'on' && !resources.length) {
          healthCls = 'badge--warn';
          healthLbl = '⚠️ No Resources';
        } else if (elecRate > 10) {
          healthCls = 'badge--danger';
          healthLbl = '🔴 High Draw';
        } else if (elecRate > 3) {
          healthCls = 'badge--warn';
          healthLbl = '⚠️ Monitor';
        } else {
          healthCls = 'badge--ok';
          healthLbl = '✅ Good';
        }
        const canToggle = <?php echo in_array($user['role'] ?? 'guest', ['owner', 'tenant']) ? 'true' : 'false'; ?>;
        const icons = {
          electricity: '⚡',
          water: '💧',
          gas: '🔥',
          solar: '☀️'
        };
        const canEdit = <?php echo $user['role'] === 'owner' ? 'true' : 'false'; ?>;
        const resHtml = (d.resources || []).filter(r => r.resource_type)
          .map(r => `<span style="display:inline-flex;align-items:center;gap:3px;margin-right:6px;font-size:.78rem">
        ${icons[r.resource_type] || '🔌'}
        ${parseFloat(r.consumption_rate).toFixed(2)}
        <span style="color:var(--text-3);font-size:.7rem">${r.unit}/h</span>
      </span>`).join('') || '<span class="text-muted">—</span>';
        return `
      <tr data-id="${d.id}" data-status="${d.status}" data-location="${(d.location||'').toLowerCase()}">
        <td><span style="font-size:1.1rem;margin-right:6px">${d.icon||'🔌'}</span><strong>${htmlEsc(d.name)}</strong></td>
        <td class="text-muted">${htmlEsc(d.location || '—')}</td>
        <td><span class="badge ${statusCls}">${statusLbl}</span></td>
        <td>${resHtml}</td>
       <td>
</td>
        
        <td><span class="badge ${healthCls}">${healthLbl}</span></td>
        <td>
          <label class="toggle">
            <input type="checkbox" ${d.status==='on'?'checked':''} ${canToggle?'':'disabled'} onchange="toggleDevice(${d.id}, this)" />
            <span class="toggle__slider"></span>
          </label>
        </td>
        <td>
          <div style="display:flex;gap:6px">
            <button class="btn btn--sm btn--outline" onclick="openEditModal(${d.id})">✏️</button>
            <button class="btn btn--sm btn--outline" onclick="confirmDelete(${d.id},'${htmlEsc(d.name)}')" style="color:var(--danger);border-color:var(--danger)">🗑️</button>
          </div>
        </td>
      </tr>`;
      }).join('');
    }

    function populateLocationFilter(devices) {
      const locations = [...new Set(devices.map(d => d.location).filter(Boolean))].sort();
      const sel = document.getElementById('filter-location');
      sel.innerHTML = '<option value="">All Rooms</option>' + locations.map(l => `<option value="${l.toLowerCase()}">${htmlEsc(l)}</option>`).join('');
    }

    function applyFilters() {
      const loc = document.getElementById('filter-location').value.toLowerCase();
      const status = document.getElementById('filter-status').value.toLowerCase();
      document.querySelectorAll('#devices-tbody tr[data-id]').forEach(row => {
        const show = (!loc || row.dataset.location === loc) && (!status || row.dataset.status === status);
        row.style.display = show ? '' : 'none';
      });
    }

    /* ── Charts ── */
    function renderUsageChart(devices) {
      const top = devices.slice(0, 8);
      const labels = top.map(d => d.name.length > 10 ? d.name.slice(0, 10) + '…' : d.name);

      const resourceConfig = {
        electricity: {
          label: '⚡ Electricity (kWh)',
          color: '#60a5fa'
        },
        water: {
          label: '💧 Water (L)',
          color: '#34d399'
        },
        gas: {
          label: '🔥 Gas (m³)',
          color: '#fb923c'
        },
      };

      const datasets = [];

      for (const [rType, cfg] of Object.entries(resourceConfig)) {
        const data = top.map(d => {
          if (d.type === 'solar_panel') return 0;
          const res = (d.resources || []).find(r => r.resource_type === rType);
          return res ? parseFloat(res.consumption_rate) : 0;
        });
        if (data.some(v => v > 0)) {
          datasets.push({
            label: cfg.label,
            data,
            color: cfg.color
          });
        }
      }
      // Solar generation dataset
      const solarData = top.map(d => {
        const res = (d.resources || []).find(r => r.resource_type === 'solar');
        return res ? parseFloat(res.consumption_rate) : 0;
      });

      if (solarData.some(v => v > 0)) {
        datasets.push({
          label: '☀️ Solar Generation (kWh)',
          data: solarData,
          color: '#facc15'
        });
      }

      if (!datasets.length) {
        makeBarChart('chart-appliance-bar', {
          labels,
          datasets: [{
            label: 'No data',
            data: top.map(() => 0),
            color: '#334155'
          }]
        });
        return;
      }

      makeBarChart('chart-appliance-bar', {
        labels,
        datasets
      });
    }

    function renderVampireChart(devices) {
      const totals = {
        electricity: 0,
        water: 0,
        gas: 0,
        solar: 0
      };

      devices.forEach(d => {
        (d.resources || []).forEach(r => {
          if (totals[r.resource_type] !== undefined) {
            totals[r.resource_type] += parseFloat(r.consumption_rate || 0);
          }
        });
      });

      const labels = [];
      const data = [];
      const colors = [];

      const config = {
        electricity: {
          label: '⚡ Electricity (kWh)',
          color: '#60a5fa'
        },
        water: {
          label: '💧 Water (L)',
          color: '#34d399'
        },
        gas: {
          label: '🔥 Gas (m³)',
          color: '#fb923c'
        },
        solar: {
          label: '☀️ Solar (kWh)',
          color: '#facc15'
        },
      };

      for (const [key, cfg] of Object.entries(config)) {
        if (totals[key] > 0) {
          labels.push(cfg.label);
          data.push(parseFloat(totals[key].toFixed(2)));
          colors.push(cfg.color);
        }
      }

      if (!data.length) {
        makeDoughnutChart('chart-vampire', {
          labels: ['No resource data'],
          data: [1],
          colors: ['#334155']
        });
        return;
      }

      makeDoughnutChart('chart-vampire', {
        labels,
        data,
        colors
      });
    } /* ── Toggle ── */
    async function toggleDevice(id, checkbox) {
      checkbox.disabled = true;
      try {
        const fd = new FormData();
        fd.append('action', 'toggle');
        fd.append('id', id);
        const data = await (await fetch(API_DEVICES, {
          method: 'POST',
          body: fd
        })).json();
        if (!data.ok) {
          showToast('Toggle failed: ' + (data.msg || ''), 'danger');
          checkbox.checked = !checkbox.checked;
          return;
        }
        const newStatus = data.status;
        checkbox.checked = newStatus === 'on';
        const row = checkbox.closest('tr');
        if (row) {
          row.dataset.status = newStatus;
          const badge = row.querySelector('.badge');
          if (badge) {
            badge.className = `badge ${newStatus==='on'?'badge--ok':newStatus==='standby'?'badge--warn':'badge--neutral'}`;
            badge.textContent = newStatus === 'on' ? 'Active' : newStatus === 'standby' ? 'Standby' : 'Off';
          }
        }
        const dev = allDevices.find(d => d.id == id);
        if (dev) dev.status = newStatus;
        showToast(`${data.device?.name||'Device'} turned ${newStatus.toUpperCase()}`, newStatus === 'on' ? 'ok' : 'info');
        const liveKwh = allDevices.filter(d => d.status === 'on').reduce((sum, d) => {
          const elec = (d.resources || []).find(r => r.resource_type === 'electricity');
          return sum + parseFloat(elec?.consumption_rate || 0);
        }, 0);
        document.getElementById('kpi-draw').innerHTML = `${liveKwh.toFixed(2)} <small style="font-size:1rem">kW</small>`;
      } catch (e) {
        showToast('Network error', 'danger');
        checkbox.checked = !checkbox.checked;
      } finally {
        checkbox.disabled = false;
      }
    }

    /* ── Add Modal ── */
    function openAddModal() {
      ['add-name', 'add-location', 'add-category', 'add-icon'].forEach(id => {
        document.getElementById(id).value = '';
      });
      ['electricity', 'water', 'gas', 'solar'].forEach(r => {
        document.getElementById('res-' + r).checked = false;
        document.getElementById('rate-' + r).value = '';
        document.getElementById('rate-' + r).disabled = true;
      });
      document.getElementById('err-name').textContent = '';
      prefillDefaults();
      document.getElementById('modal-add').style.display = 'flex';
    }

    function closeAddModal() {
      document.getElementById('modal-add').style.display = 'none';
    }

    function prefillDefaults() {
      const def = TYPE_DEFAULTS[document.getElementById('add-type').value] || TYPE_DEFAULTS.generic;
      document.getElementById('add-category').value = def.category;
      document.getElementById('add-icon').value = def.icon;
      document.getElementById('add-location').value = def.location;
      // Reset all first
      ['electricity', 'water', 'gas', 'solar'].forEach(r => {
        const cb = document.getElementById('res-' + r);
        const rate = document.getElementById('rate-' + r);
        if (cb && rate) {
          cb.checked = false;
          rate.disabled = true;
          rate.value = '';
        }
      });

      // Check the right ones based on type defaults
      (def.resources || []).forEach(r => {
        const cb = document.getElementById('res-' + r.type);
        const rate = document.getElementById('rate-' + r.type);
        if (cb && rate) {
          cb.checked = true;
          rate.disabled = false;
          rate.value = r.rate;
        }
      });
    }

    async function saveDevice() {
      const name = document.getElementById('add-name').value.trim();
      if (!name) {
        document.getElementById('err-name').textContent = 'Device name is required.';
        document.getElementById('err-name').style.display = 'block';
        return;
      }
      document.getElementById('err-name').style.display = 'none';
      document.getElementById('save-txt').style.display = 'none';
      document.getElementById('save-spin').style.display = '';
      document.getElementById('btn-save-device').disabled = true;
      try {
        const fd = new FormData();
        fd.append('action', 'create');
        fd.append('name', name);
        fd.append('type', document.getElementById('add-type').value);
        fd.append('location', document.getElementById('add-location').value.trim());
        fd.append('category', document.getElementById('add-category').value.trim());
        fd.append('icon', document.getElementById('add-icon').value.trim());
        fd.append('status', 'off');
        fd.append('wattage', TYPE_DEFAULTS[document.getElementById('add-type').value]?.wattage ?? 100);
        ['electricity', 'water', 'gas', 'solar'].forEach(r => {
          const cb = document.getElementById('res-' + r);
          if (cb?.checked) {
            const rateVal = parseFloat(document.getElementById('rate-' + r).value) || 0;
            fd.append('res_' + r, '1');
            fd.append('rate_' + r, rateVal);
          }
        });
        const data = await (await fetch(API_DEVICES, {
          method: 'POST',
          body: fd
        })).json();
        if (!data.ok) {
          showToast('Error: ' + (data.msg || 'Could not save device'), 'danger');
          return;
        }
        showToast(`✅ ${name} added successfully!`, 'ok');
        closeAddModal();
        loadDevices();
      } catch (e) {
        showToast('Network error saving device', 'danger');
      } finally {
        document.getElementById('save-txt').style.display = '';
        document.getElementById('save-spin').style.display = 'none';
        document.getElementById('btn-save-device').disabled = false;
      }
    }

    /* ── Edit Modal ── */
    function openEditModal(id) {
      const dev = allDevices.find(d => d.id == id);
      if (!dev) {
        showToast('Device not found', 'warn');
        return;
      }
      document.getElementById('edit-id').value = dev.id;
      document.getElementById('edit-name').value = dev.name;
      document.getElementById('edit-location').value = dev.location || '';
      document.getElementById('edit-category').value = dev.category || '';
      // Pre-check resources
      ['electricity', 'water', 'gas', 'solar'].forEach(r => {
        const existingRes = (dev.resources || []).find(res => res.resource_type === r);
        const cb = document.getElementById('edit-res-' + r);
        const rate = document.getElementById('edit-rate-' + r);
        if (cb && rate) {
          cb.checked = !!existingRes;
          rate.disabled = !existingRes;
          rate.value = existingRes ? existingRes.consumption_rate : '';
        }
      });
      document.getElementById('modal-edit').style.display = 'flex';
    }

    function closeEditModal() {
      document.getElementById('modal-edit').style.display = 'none';
    }

    async function updateDevice() {
      const id = document.getElementById('edit-id').value;
      if (!id) {
        showToast('Missing device id', 'danger');
        return;
      }
      const fd = new FormData();
      fd.append('action', 'update');
      fd.append('id', id);
      fd.append('name', document.getElementById('edit-name').value.trim());
      fd.append('location', document.getElementById('edit-location').value.trim());
      fd.append('category', document.getElementById('edit-category').value.trim());
      console.log('resources being sent:');
      ['electricity', 'water', 'gas', 'solar'].forEach(r => {
        const cb = document.getElementById('edit-res-' + r);
        const rate = document.getElementById('edit-rate-' + r);
        console.log(r, 'checked:', cb?.checked, 'rate:', rate?.value);
        if (cb?.checked) {
          fd.append('res_' + r, '1');
          fd.append('rate_' + r, parseFloat(rate?.value) || 0);
        }
      });
      try {
        const data = await (await fetch(API_DEVICES, {
          method: 'POST',
          body: fd
        })).json();
        if (!data.ok) {
          showToast('Update failed: ' + data.msg, 'danger');
          return;
        }
        showToast('Device updated ✅', 'ok');
        closeEditModal();
        loadDevices();
      } catch (e) {
        showToast('Network error', 'danger');
      }
    }

    /* ── Delete ── */
    function confirmDelete(id, name) {
      openModal(`
        <div class="modal__header">
          <div class="modal__title">Delete Device</div>
          <button class="modal__close" onclick="closeModal()">✕</button>
        </div>
        <div class="alert alert--danger" style="margin-bottom:var(--sp-lg)">
          <span class="alert__icon">⚠️</span>
          <div><div class="alert__title">Are you sure?</div><div class="alert__msg">This will permanently delete <strong>${htmlEsc(name)}</strong> and all its usage history.</div></div>
        </div>
        <div style="display:flex;gap:var(--sp-sm);justify-content:flex-end">
          <button class="btn btn--outline" onclick="closeModal()">Cancel</button>
          <button class="btn btn--danger" onclick="deleteDevice(${id})">Delete Device</button>
        </div>`);
    }

    async function deleteDevice(id) {
      closeModal();
      const fd = new FormData();
      fd.append('action', 'delete');
      fd.append('id', id);
      try {
        const data = await (await fetch(API_DEVICES, {
          method: 'POST',
          body: fd
        })).json();
        if (!data.ok) {
          showToast('Delete failed: ' + data.msg, 'danger');
          return;
        }
        showToast('Device deleted', 'ok');
        loadDevices();
      } catch (e) {
        showToast('Network error', 'danger');
      }
    }

    /* ── Utility ── */
    function htmlEsc(str) {
      return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function toggleRate(checkbox, type) {
      document.getElementById('rate-' + type).disabled = !checkbox.checked;
    }

    function toggleEditRate(checkbox, type) {
      document.getElementById('edit-rate-' + type).disabled = !checkbox.checked;
    }
  </script>
</body>

</html>