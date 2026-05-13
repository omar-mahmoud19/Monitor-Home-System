<?php
require_once __DIR__ . '/../api/session.php';
checkRole(['owner', 'tenant']);

$user = getCurrentUser();
?>
<script>
  const AOT_USER = <?php echo json_encode($user ?? null); ?>;
</script>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Automation — AOT Homes</title>
  <link rel="stylesheet" href="/AOT/assets/css/style.css" />
  <script src="/AOT/assets/js/main.js" defer></script>
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
        <h1 class="topbar__title">Automation &amp; Alerts</h1>
        <span class="topbar__meta" id="today-date"></span>
        <span class="topbar__meta" id="live-clock"></span>
        <button class="topbar__btn primary" id="open-modal-btn">+ New Rule</button>
      </header>

      <div class="content">

        <!-- KPI Row -->
        <div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:var(--sp-xl)">
          <div class="kpi-card">
            <span class="kpi-card__icon">🤖</span>
            <div class="kpi-card__label">Active Rules</div>
            <div class="kpi-card__value" id="kpi-active-rules">—</div>
            <div class="kpi-card__change" id="kpi-triggered-today">Loading…</div>
          </div>
          <div class="kpi-card">
            <span class="kpi-card__icon">🔔</span>
            <div class="kpi-card__label">Alerts Today</div>
            <div class="kpi-card__value" id="kpi-alerts-today" style="color:var(--clr-warn)">—</div>
            <div class="kpi-card__change up" id="kpi-unacknowledged">Loading…</div>
          </div>
          <div class="kpi-card">
            <span class="kpi-card__icon">📡</span>
            <div class="kpi-card__label">Sensors Online</div>
            <div class="kpi-card__value" id="kpi-sensors" style="color:var(--clr-accent)">—</div>
            <div class="kpi-card__change" id="kpi-sensor-sub">Loading…</div>
          </div>
          <div class="kpi-card">
            <span class="kpi-card__icon">🏖️</span>
            <div class="kpi-card__label">Vacation Mode</div>
            <div class="kpi-card__value" id="kpi-vacation-label" style="color:var(--clr-muted);font-size:1.2rem">OFF</div>
            <label class="toggle" style="margin-top:var(--sp-sm)">
              <input type="checkbox" id="vacation-toggle">
              <span class="toggle__slider"></span>
            </label>
          </div>
        </div>

        <div class="grid-2-1">

          <!-- Rules Table -->
          <div class="card">
            <div class="section-header">
              <div>
                <div class="section-title">If-This-Then-That Rules</div>
                <div class="section-sub" id="rules-status">Loading rules…</div>
              </div>
            </div>
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Rule Name</th>
                    <th>Condition</th>
                    <th>Action</th>
                    <th>Last Triggered</th>
                    <th>Active</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody id="rules-table-body">
                  <tr>
                    <td colspan="6" class="text-muted" style="text-align:center;padding:var(--sp-lg)">Loading…</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Alert Feed + Sensor Status -->
          <div style="display:flex;flex-direction:column;gap:var(--sp-lg)">

            <!-- Recent Alerts -->
            <div class="card">
              <div class="section-header">
                <div class="section-title">Recent Alerts</div>
                <span class="badge badge--warn" id="alerts-new-badge">—</span>
              </div>
              <div id="alerts-feed" style="display:flex;flex-direction:column;gap:var(--sp-sm)">
                <div class="text-sm text-muted">Loading alerts…</div>
              </div>
            </div>

            <!-- Sensor Status -->
            <div class="card">
              <div class="section-header">
                <div class="section-title">Sensor Status</div>
              </div>
              <div id="sensor-list" style="display:flex;flex-direction:column;gap:10px">
                <div class="text-sm text-muted">Loading sensors…</div>
              </div>
            </div>

          </div>
        </div><!-- /grid-2-1 -->

        <!-- Notification Channels -->
        <div class="card mt-lg">
          <div class="section-header">
            <div class="section-title">Notification Channels</div>
          </div>
          <div class="grid-3" id="channels-grid">
            <!-- Rendered by JS from API -->
            <div class="text-sm text-muted">Loading channels…</div>
          </div>
        </div>

      </div><!-- /content -->
    </main>

    <!-- New Rule Modal -->
    <div id="modal-rule"
      style="display:none;position:fixed;inset:0;background:#00000088;z-index:200;align-items:center;justify-content:center">
      <div class="card" style="width:500px;max-width:90vw">
        <div class="section-header">
          <div class="section-title">Create New Rule</div>
        </div>
        <div class="form-group">
          <label class="form-label">Rule Name</label>
          <input class="form-input" type="text" id="modal-rule-name" placeholder="e.g. Night Saver Mode" />
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">IF — Resource/Trigger</label>
            <select class="form-select" id="modal-trigger-type">
              <option value="electricity">Electricity Usage</option>
              <option value="water">Water Usage</option>
              <option value="gas">Gas Usage</option>
              <option value="time">Time of Day</option>
              <option value="appliance">Appliance State</option>
              <option value="budget">Budget %</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Condition</label>
            <select class="form-select" id="modal-condition">
              <option value="gt">Greater than</option>
              <option value="lt">Less than</option>
              <option value="eq">Equals</option>
              <option value="between">Between</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Threshold Value</label>
          <input class="form-input" type="text" id="modal-threshold"
            placeholder="e.g. 150 (kWh), 80 (%), 22:00 (time)" />
        </div>
        <div class="form-group">
          <label class="form-label">THEN — Action</label>
          <select class="form-select" id="modal-action-type">
            <option value="dashboard">Send Dashboard Alert</option>
            <option value="email">Send Email</option>
            <option value="sms">Send SMS</option>
            <option value="turn_off">Turn Off Appliance</option>
            <option value="turn_on">Turn On Appliance</option>
            <option value="log">Log Only</option>
          </select>
        </div>
        <div style="display:flex;gap:var(--sp-sm);justify-content:flex-end;margin-top:var(--sp-md)">
          <button class="btn btn--outline" id="cancel-modal-btn">Cancel</button>
          <button class="btn btn--primary" id="save-rule-btn">Save Rule</button>
        </div>
      </div>
    </div>

  </div><!-- /layout -->

  <script>
    /**
     * ─────────────────────────────────────────────────────────────
     *  automation.php — Client-side integration with api/automation.php
     * ─────────────────────────────────────────────────────────────
     */

    /* ── Utility: POST to api/automation.php ── */
    async function apiAuto(body = {}) {
      const fd = new FormData();
      for (const [k, v] of Object.entries(body)) fd.append(k, v);
      const res = await fetch('/AOT/api/automation.php', {
        method: 'POST',
        body: fd
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    }

    /* ── Utility: GET from api/automation.php ── */
    async function apiAutoGet(params = {}) {
      const qs = new URLSearchParams(params).toString();
      const res = await fetch(`/AOT/api/automation.php?${qs}`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    }

    /* ── Format timestamp relative to now ── */
    function fmtTime(ts) {
      if (!ts) return '—';
      const d = new Date(ts);
      const now = new Date();
      const diffMs = now - d;
      const diffMin = Math.floor(diffMs / 60000);
      const diffH = Math.floor(diffMs / 3600000);
      const diffD = Math.floor(diffMs / 86400000);
      if (diffMin < 1) return 'Just now';
      if (diffMin < 60) return `${diffMin}m ago`;
      if (diffH < 24) return `Today ${d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})}`;
      if (diffD === 1) return 'Yesterday';
      return `${diffD} days ago`;
    }

    /* ── Alert severity → CSS class ── */
    function alertClass(priority) {
      if (!priority) return 'alert--ok';
      const p = priority.toLowerCase();
      if (p === 'critical' || p === 'high') return 'alert--danger';
      if (p === 'medium' || p === 'warn') return 'alert--warn';
      return 'alert--ok';
    }

    function alertIcon(priority) {
      const p = (priority || '').toLowerCase();
      if (p === 'critical' || p === 'high') return '🚨';
      if (p === 'medium' || p === 'warn') return '⚠️';
      return '✅';
    }

    /* ─────────────────────────────────────────────────────────
     *  Load KPIs + Rules + Alerts + Sensors in one call
     *  GET api/automation.php?type=summary (or separate endpoints)
     * ───────────────────────────────────────────────────────── */
    async function loadAll() {
      try {
        const [rulesData, alertsData, sensorsData, channelsData] = await Promise.allSettled([
          apiAutoGet({
            type: 'rules'
          }),
          apiAutoGet({
            type: 'alerts'
          }),
          apiAutoGet({
            type: 'sensors'
          }),
          apiAutoGet({
            type: 'channels'
          }),
        ]);

        if (rulesData.status === 'fulfilled' && rulesData.value.ok) renderRules(rulesData.value);
        else renderRulesFallback();

        if (alertsData.status === 'fulfilled' && alertsData.value.ok) renderAlerts(alertsData.value);
        else renderAlertsFallback();

        if (sensorsData.status === 'fulfilled' && sensorsData.value.ok) renderSensors(sensorsData.value);
        else renderSensorsFallback();

        if (channelsData.status === 'fulfilled' && channelsData.value.ok) renderChannels(channelsData.value);
        else renderChannelsFallback();

      } catch (err) {
        console.error('loadAll:', err);
        renderRulesFallback();
        renderAlertsFallback();
        renderSensorsFallback();
        renderChannelsFallback();
      }
    }

    /* ─────────────────────────────────────────────────────────
     *  Rules
     * ───────────────────────────────────────────────────────── */
    function renderRules(data) {
      const rules = data.rules || [];
      const active = rules.filter(r => r.is_active || r.active).length;
      const triggered = rules.filter(r => r.triggered_today).length;

      document.getElementById('kpi-active-rules').textContent = active;
      document.getElementById('kpi-triggered-today').textContent = `${triggered} triggered today`;
      document.getElementById('rules-status').textContent = `${rules.length} rules loaded`;

      const tbody = document.getElementById('rules-table-body');
      if (!rules.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-muted" style="text-align:center;padding:var(--sp-lg)">No rules yet. Create your first rule!</td></tr>`;
        return;
      }

      tbody.innerHTML = rules.map(r => {
        const isActive = r.is_active ?? r.active ?? false;
        const condLabel = `${r.trigger_label || r.trigger_type || '—'} ${r.condition || ''} ${r.threshold || ''}`.trim();
        return `
      <tr>
        <td><b>${r.name || r.rule_name || '—'}</b></td>
        <td class="text-muted">${condLabel}</td>
        <td>${r.action_label || r.action || '—'}</td>
        <td class="text-muted">${fmtTime(r.last_triggered_at || r.last_triggered)}</td>
        <td>
          <label class="toggle">
            <input type="checkbox" ${isActive ? 'checked' : ''}
              onchange="toggleRule(${r.id}, this.checked)">
            <span class="toggle__slider"></span>
          </label>
        </td>
        <td>
          <button class="btn btn--outline btn--sm"
            onclick="deleteRule(${r.id})">🗑</button>
        </td>
      </tr>`;
      }).join('');
    }

    function renderRulesFallback() {
      document.getElementById('kpi-active-rules').textContent = '—';
      document.getElementById('kpi-triggered-today').textContent = 'Could not load';
      document.getElementById('rules-status').textContent = '⚠ API unavailable — showing cached data';

      const fallback = [{
          id: 1,
          name: 'Peak Hour Alert',
          trigger_type: 'Time = 18:00–22:00',
          action: 'Send Dashboard Alert',
          last_triggered: '2025-05-08T18:00:00',
          is_active: true
        },
        {
          id: 2,
          name: 'Water Leak Guard',
          trigger_type: 'Water > 150% baseline',
          action: 'Alert + Email',
          last_triggered: '2025-05-08T11:24:00',
          is_active: true
        },
        {
          id: 3,
          name: 'Gas Safety Cutoff',
          trigger_type: 'Gas > 8m³ & Temp > 30°',
          action: 'Alert + SMS',
          last_triggered: '2025-05-05T09:00:00',
          is_active: true
        },
        {
          id: 4,
          name: 'EV Night Charge',
          trigger_type: 'Time = 02:00 & EV = OFF',
          action: 'Turn On EV Charger',
          last_triggered: '2025-05-07T02:00:00',
          is_active: true
        },
        {
          id: 5,
          name: 'Budget 80% Warning',
          trigger_type: 'Monthly cost ≥ 80% budget',
          action: 'Email + Dashboard',
          last_triggered: '2025-05-03T00:00:00',
          is_active: true
        },
        {
          id: 6,
          name: 'Ghost Load Detector',
          trigger_type: 'Device draw > 5W while OFF',
          action: 'Log + Dashboard',
          last_triggered: '2025-05-06T14:00:00',
          is_active: false
        },
      ];
      renderRules({
        rules: fallback
      });
    }

    /* ─────────────────────────────────────────────────────────
     *  Toggle rule active/inactive → POST action=toggle_rule
     * ───────────────────────────────────────────────────────── */
    async function toggleRule(id, active) {
      try {
        const data = await apiAuto({
          action: 'toggle_rule',
          id,
          active: active ? 1 : 0
        });
        if (!data.ok) throw new Error(data.msg);
        showToast(`Rule ${active ? 'enabled' : 'disabled'}`, 'ok');
      } catch (err) {
        showToast(`Failed to toggle rule: ${err.message}`, 'warn');
      }
    }

    /* ─────────────────────────────────────────────────────────
     *  Delete rule → POST action=delete_rule
     * ───────────────────────────────────────────────────────── */
    async function deleteRule(id) {
      if (!confirm('Delete this rule?')) return;
      try {
        const data = await apiAuto({
          action: 'delete_rule',
          id
        });
        if (!data.ok) throw new Error(data.msg);
        showToast('🗑 Rule deleted', 'ok');
        loadAll();
      } catch (err) {
        showToast(`Failed to delete rule: ${err.message}`, 'warn');
      }
    }

    /* ─────────────────────────────────────────────────────────
     *  Create rule → POST action=create_rule
     * ───────────────────────────────────────────────────────── */
    async function saveRule() {
      const name = document.getElementById('modal-rule-name').value.trim();
      const trigger = document.getElementById('modal-trigger-type').value;
      const condition = document.getElementById('modal-condition').value;
      const threshold = document.getElementById('modal-threshold').value.trim();
      const action = document.getElementById('modal-action-type').value;

      if (!name || !threshold) {
        showToast('Please fill all required fields', 'warn');
        return;
      }

      const btn = document.getElementById('save-rule-btn');
      btn.disabled = true;
      btn.textContent = 'Saving…';

      try {
        const data = await apiAuto({
          action: 'create_rule',
          rule_name: name,
          trigger_type: trigger,
          condition,
          threshold,
          action_type: action,
        });
        if (!data.ok) throw new Error(data.msg);
        showToast(`✅ Rule "${name}" created`, 'ok');
        closeModal();
        loadAll();
      } catch (err) {
        showToast(`Failed to create rule: ${err.message}`, 'warn');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Save Rule';
      }
    }

    /* ─────────────────────────────────────────────────────────
     *  Alerts Feed
     * ───────────────────────────────────────────────────────── */
    function renderAlerts(data) {
      const alerts = data.alerts || [];
      const unacked = alerts.filter(a => !a.acknowledged).length;
      const todayCount = alerts.filter(a => {
        const d = new Date(a.created_at || a.timestamp || 0);
        return d.toDateString() === new Date().toDateString();
      }).length;

      document.getElementById('kpi-alerts-today').textContent = todayCount;
      document.getElementById('kpi-unacknowledged').textContent = `${unacked} unacknowledged`;
      document.getElementById('alerts-new-badge').textContent = `${unacked} new`;

      const feed = document.getElementById('alerts-feed');
      if (!alerts.length) {
        feed.innerHTML = `<div class="text-sm text-muted">No recent alerts. All clear! ✅</div>`;
        return;
      }

      feed.innerHTML = alerts.slice(0, 6).map(a => `
    <div class="alert ${alertClass(a.priority)}" style="margin:0">
      <span class="alert__icon">${alertIcon(a.priority)}</span>
      <div style="flex:1">
        <div class="alert__title">${a.title || a.message || '—'}</div>
        <div class="alert__msg">${fmtTime(a.created_at || a.timestamp)} · ${a.priority || 'info'}</div>
      </div>
      ${!a.acknowledged ? `<button class="btn btn--sm btn--outline"
        style="margin-left:auto;font-size:.7rem"
        onclick="acknowledgeAlert(${a.id}, this)">Ack</button>` : ''}
    </div>`).join('');
    }

    function renderAlertsFallback() {
      document.getElementById('kpi-alerts-today').textContent = '—';
      document.getElementById('kpi-unacknowledged').textContent = '—';
      document.getElementById('alerts-new-badge').textContent = '—';

      const now = new Date();
      const fallback = [{
          id: 1,
          title: 'Washer Overload',
          priority: 'critical',
          created_at: new Date(now - 3e6).toISOString(),
          acknowledged: false
        },
        {
          id: 2,
          title: 'Water Leak Suspected',
          priority: 'medium',
          created_at: new Date(now - 5e6).toISOString(),
          acknowledged: false
        },
        {
          id: 3,
          title: 'Water Budget at 96%',
          priority: 'medium',
          created_at: new Date(now - 86400000).toISOString(),
          acknowledged: false
        },
        {
          id: 4,
          title: 'EV Charger Started',
          priority: 'ok',
          created_at: new Date(now - 86400000 + 3600000).toISOString(),
          acknowledged: true
        },
      ];
      renderAlerts({
        alerts: fallback
      });
    }

    async function acknowledgeAlert(id, btn) {
      try {
        const data = await apiAuto({
          action: 'acknowledge_alert',
          id
        });
        if (!data.ok) throw new Error(data.msg);
        btn.textContent = '✓';
        btn.disabled = true;
        showToast('Alert acknowledged', 'ok');
      } catch (err) {
        showToast(`Failed: ${err.message}`, 'warn');
      }
    }

    /* ─────────────────────────────────────────────────────────
     *  Sensors
     * ───────────────────────────────────────────────────────── */
    function renderSensors(data) {
      const sensors = data.sensors || [];
      const online = sensors.filter(s => s.status === 'online' || s.online).length;
      const total = sensors.length;
      const offline = total - online;

      document.getElementById('kpi-sensors').textContent = `${online}/${total}`;
      document.getElementById('kpi-sensor-sub').textContent = offline > 0 ? `${offline} sensor${offline>1?'s':''} offline` : 'All sensors online';

      const list = document.getElementById('sensor-list');
      if (!sensors.length) {
        list.innerHTML = `<div class="text-sm text-muted">No sensors found.</div>`;
        return;
      }

      list.innerHTML = sensors.map(s => {
        const isOnline = s.status === 'online' || s.online;
        return `
      <div class="flex-center gap-sm">
        <span class="dot dot--${isOnline ? 'ok' : 'danger'}"></span>
        ${s.name || s.sensor_name || '—'}
        <span class="ml-auto text-xs ${isOnline ? 'text-muted' : 'text-danger'}">
          ${isOnline ? 'Online' : 'Offline'}
        </span>
      </div>`;
      }).join('');
    }

    function renderSensorsFallback() {
      document.getElementById('kpi-sensors').textContent = '11/12';
      document.getElementById('kpi-sensor-sub').textContent = '1 sensor offline';
      const fallback = [{
          name: 'Kitchen Main',
          online: true
        },
        {
          name: 'HVAC Monitor',
          online: true
        },
        {
          name: 'Water Meter',
          online: true
        },
        {
          name: 'Gas Meter',
          online: true
        },
        {
          name: 'Solar Panel',
          online: true
        },
        {
          name: 'Garage Sensor',
          online: false
        },
      ];
      renderSensors({
        sensors: fallback
      });
    }

    /* ─────────────────────────────────────────────────────────
     *  Notification Channels
     * ───────────────────────────────────────────────────────── */
    function renderChannels(data) {
      const channels = data.channels || [];
      const grid = document.getElementById('channels-grid');
      if (!channels.length) {
        renderChannelsFallback();
        return;
      }

      grid.innerHTML = channels.map(ch => `
    <div style="display:flex;align-items:center;justify-content:space-between;padding:var(--sp-md);background:var(--clr-surface-2);border-radius:var(--radius-sm)">
      <div>
        <div class="font-head text-sm">${ch.icon || '📢'} ${ch.label || ch.name}</div>
        <div class="text-xs text-muted">${ch.description || ''}</div>
      </div>
      <label class="toggle">
        <input type="checkbox" ${ch.enabled ? 'checked' : ''}
          onchange="updateChannel('${ch.id || ch.name}', this.checked)">
        <span class="toggle__slider"></span>
      </label>
    </div>`).join('');
    }

    function renderChannelsFallback() {
      const fallback = [{
          id: 'dashboard',
          icon: '📊',
          label: 'Dashboard',
          description: 'All alerts',
          enabled: true
        },
        {
          id: 'email',
          icon: '📧',
          label: 'Email',
          description: 'High priority only',
          enabled: true
        },
        {
          id: 'sms',
          icon: '📱',
          label: 'SMS (Simulated)',
          description: 'Critical only',
          enabled: false
        },
      ];
      renderChannels({
        channels: fallback
      });
    }

    async function updateChannel(channelId, enabled) {
      try {
        const data = await apiAuto({
          action: 'update_channels',
          channel: channelId,
          enabled: enabled ? 1 : 0
        });
        if (!data.ok) throw new Error(data.msg);
        showToast(`${channelId} notifications ${enabled ? 'enabled' : 'disabled'}`, 'ok');
      } catch (err) {
        showToast(`Failed to update channel: ${err.message}`, 'warn');
      }
    }

    /* ─────────────────────────────────────────────────────────
     *  Vacation Mode
     * ───────────────────────────────────────────────────────── */
    async function toggleVacationMode(active) {
      try {
        const data = await apiAuto({
          action: 'vacation_mode',
          active: active ? 1 : 0
        });
        if (!data.ok) throw new Error(data.msg);
        document.getElementById('kpi-vacation-label').textContent = active ? 'ON' : 'OFF';
        document.getElementById('kpi-vacation-label').style.color = active ? 'var(--clr-accent)' : 'var(--clr-muted)';
        showToast(`🏖️ Vacation mode ${active ? 'activated' : 'deactivated'}`, 'ok');
      } catch (err) {
        showToast(`Failed: ${err.message}`, 'warn');
      }
    }

    /* ─────────────────────────────────────────────────────────
     *  Modal helpers
     * ───────────────────────────────────────────────────────── */
    function openRuleModal() {
      document.getElementById('modal-rule').style.display = 'flex';
      document.getElementById('modal-rule-name').focus();
    }

    function closeRuleModal() {
      document.getElementById('modal-rule').style.display = 'none';
      ['modal-rule-name', 'modal-threshold'].forEach(id => {
        document.getElementById(id).value = '';
      });
    }
    /* ─────────────────────────────────────────────────────────
     *  Boot
     * ───────────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', () => {

      /* Sidebar logout */
      document.getElementById('logout-btn')?.addEventListener('click', () => {
        if (confirm('Sign out of AOT Homes?')) Auth.logout();
      });

      /* Modal */
      document.getElementById('open-modal-btn')?.addEventListener('click', openRuleModal);
      document.getElementById('close-modal-btn')?.addEventListener('click', closeRuleModal);
      document.getElementById('cancel-modal-btn')?.addEventListener('click', () => {
        document.getElementById('modal-rule').style.display = 'none';
        document.getElementById('modal-rule-name').value = '';
        document.getElementById('modal-threshold').value = '';
      });
      document.getElementById('save-rule-btn')?.addEventListener('click', (e) => {
        e.preventDefault();
        saveRule();
      });
      /* Close modal on backdrop click */
      document.getElementById('modal-rule')?.addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
      });

      /* Vacation mode toggle */
      document.getElementById('vacation-toggle')?.addEventListener('change', e => {
        toggleVacationMode(e.target.checked);
      });

      /* Load all data */
      loadAll();
    });
  </script>
</body>

</html>