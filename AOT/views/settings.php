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
  <title>Settings — AOT Homes</title>
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
        <h1 class="topbar__title">Settings</h1>
        <span class="topbar__meta" id="today-date"></span>
        <span class="topbar__meta" id="live-clock"></span>
        <button class="topbar__btn primary" id="save-all-btn">Save All Changes</button>
      </header>

      <div class="content">
        <div class="grid-2">

          <!-- ── Profile & Account ── -->
          <div class="card">
            <div class="section-title mb-lg">👤 Profile &amp; Account</div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">First Name</label>
                <input class="form-input" type="text" id="first_name" name="first_name"
                  value="<?php echo htmlspecialchars($user['firstName'] ?? ''); ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Last Name</label>
                <input class="form-input" type="text" id="last_name" name="last_name"
                  value="<?php echo htmlspecialchars($user['lastName'] ?? ''); ?>" />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input class="form-input" type="email" id="email" name="email"
                value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" />
            </div>
            <div class="form-group">
              <label class="form-label">Home Address</label>
              <input class="form-input" type="text" id="address" name="address"
                value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>"
                placeholder="Street, City, Country" />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Currency</label>
                <select class="form-select" id="currency" name="currency">
                  <option value="USD">USD ($)</option>
                  <option value="EUR">EUR (€)</option>
                  <option value="GBP">GBP (£)</option>
                  <option value="EGP">EGP (E£)</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Unit System</label>
                <select class="form-select" id="units" name="units">
                  <option value="metric">Metric</option>
                  <option value="imperial">Imperial</option>
                </select>
              </div>
            </div>
            <div class="card" style="grid-column: 1 / -1">
              <div class="section-title mb-lg">🏠 Home Information</div>
              <div style="display:flex;align-items:center;gap:var(--sp-lg);flex-wrap:wrap">
                <div>
                  <div class="text-xs text-muted mb-md">Home Name</div>
                  <div class="font-head" style="font-size:1.2rem">
                    <?php echo htmlspecialchars($user['homeName'] ?? 'My Home'); ?>
                  </div>
                </div>
                <div style="width:1px;height:40px;background:var(--border)"></div>
                <div>
                  <div class="text-xs text-muted mb-md">Home Code</div>
                  <div style="display:flex;align-items:center;gap:var(--sp-sm)">
                    <span class="font-head" style="font-size:1.4rem;letter-spacing:4px;color:var(--clr-accent)"
                      id="home-code-display">••••••••</span>
                    <button class="btn btn--outline btn--sm" onclick="toggleHomeCode()">👁 Show</button>
                    <button class="btn btn--outline btn--sm" onclick="copyHomeCode()">📋 Copy</button>
                  </div>
                </div>
                <div style="width:1px;height:40px;background:var(--border)"></div>
                <div>
                  <div class="text-xs text-muted mb-md">Home Password</div>
                  <div class="text-sm text-muted">Share with tenants & guests to join your home</div>
                </div>
              </div>
            </div>

            <button class="btn btn--primary btn--sm" id="update-profile-btn">Update Profile</button>

            <div class="divider"></div>
            <div class="section-title mb-lg" style="font-size:.9rem">🔑 Change Password</div>
            <div class="form-group">
              <label class="form-label">Current Password</label>
              <input class="form-input" type="password" id="current_password" />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">New Password</label>
                <input class="form-input" type="password" id="new_password" />
              </div>
              <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input class="form-input" type="password" id="confirm_password" />
              </div>
            </div>
            <button class="btn btn--outline btn--sm" id="change-password-btn">Change Password</button>
          </div>

          <!-- ── Tariff Settings ── -->
          <div class="card">
            <div class="section-title mb-lg">💲 Tariff &amp; Rate Settings</div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Peak Rate (per kWh)</label>
                <input class="form-input" type="number" step="0.01" id="rate_peak" name="rate_peak" value="0.28" />
              </div>
              <div class="form-group">
                <label class="form-label">Off-Peak Rate (per kWh)</label>
                <input class="form-input" type="number" step="0.01" id="rate_offpeak" name="rate_offpeak" value="0.12" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Peak Hours Start</label>
                <input class="form-input" type="time" id="peak_start" name="peak_start" value="18:00" />
              </div>
              <div class="form-group">
                <label class="form-label">Peak Hours End</label>
                <input class="form-input" type="time" id="peak_end" name="peak_end" value="22:00" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Water Rate (per L)</label>
                <input class="form-input" type="number" step="0.001" id="rate_water" name="rate_water" value="0.005" />
              </div>
              <div class="form-group">
                <label class="form-label">Gas Rate (per m³)</label>
                <input class="form-input" type="number" step="0.01" id="rate_gas" name="rate_gas" value="0.45" />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Solar Panel Capacity (kW)</label>
              <input class="form-input" type="number" step="0.1" id="solar_capacity" name="solar_capacity" value="4.5" />
            </div>
            <button class="btn btn--primary btn--sm" id="update-tariffs-btn">Update Tariffs</button>
          </div>

          <!-- ── User Management (RBAC) ── -->
          <div class="card">
            <div class="section-header">
              <div class="section-title">👥 User Management (RBAC)</div>
              <button class="btn btn--outline btn--sm" id="invite-user-btn">+ Invite User</button>
            </div>
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Permissions</th>
                    <th>Status</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody id="users-table-body">
                  <tr>
                    <td colspan="5" class="text-muted" style="text-align:center;padding:var(--sp-lg)">
                      Loading users…
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="divider"></div>
            <div class="text-xs text-muted">
              Child accounts cannot toggle appliances rated above 1,500W.
            </div>
          </div>

          <!-- ── Data & System ── -->
          <div class="card">
            <div class="section-title mb-lg">🗄️ Data &amp; System</div>

            <div class="form-group">
              <label class="form-label">Data Simulation Speed</label>
              <select class="form-select" id="sim_speed">
                <option value="5">Every 5 seconds</option>
                <option value="10">Every 10 seconds</option>
                <option value="30">Every 30 seconds</option>
                <option value="60">Every 1 minute</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Raw Data Retention (before archive)</label>
              <select class="form-select" id="retention">
                <option value="7">7 days</option>
                <option value="30" selected>30 days</option>
                <option value="90">90 days</option>
              </select>
            </div>

            <div class="divider"></div>

            <div style="display:flex;flex-direction:column;gap:var(--sp-md)">
              <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                  <div class="text-sm font-head">Neighborhood Benchmarking</div>
                  <div class="text-xs text-muted">Compare your usage with anonymous neighbours</div>
                </div>
                <label class="toggle">
                  <input type="checkbox" id="toggle_benchmarking" checked>
                  <span class="toggle__slider"></span>
                </label>
              </div>
              <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                  <div class="text-sm font-head">Weather Correlation</div>
                  <div class="text-xs text-muted">Adjust gas baseline based on temperature</div>
                </div>
                <label class="toggle">
                  <input type="checkbox" id="toggle_weather" checked>
                  <span class="toggle__slider"></span>
                </label>
              </div>
              <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                  <div class="text-sm font-head">Audit Trail Logging</div>
                  <div class="text-xs text-muted">Log all setting changes (recommended)</div>
                </div>
                <label class="toggle">
                  <input type="checkbox" id="toggle_audit" checked>
                  <span class="toggle__slider"></span>
                </label>
              </div>
            </div>

            <div class="divider"></div>
            <button class="btn btn--primary btn--sm" id="save-system-btn">Save System Settings</button>

            <div class="divider"></div>
            <div class="section-title mb-lg" style="color:var(--clr-danger)">⚠ Danger Zone</div>
            <div style="display:flex;gap:var(--sp-sm);flex-wrap:wrap">
              <button class="btn btn--outline btn--sm" id="purge-data-btn">Purge Raw Data</button>
              <button class="btn btn--outline btn--sm" id="reset-sim-btn">Reset Simulation</button>
              <button class="btn btn--danger btn--sm" id="delete-account-btn">Delete Account</button>
            </div>
          </div>

        </div><!-- /grid-2 -->
      </div><!-- /content -->
    </main>
  </div><!-- /layout -->

  <script>
    /* ── API helper ── */
    async function apiSettings(body = {}) {
      const fd = new FormData();
      for (const [k, v] of Object.entries(body)) fd.append(k, v);
      const res = await fetch('/AOT/api/settings.php', {
        method: 'POST',
        body: fd
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    }

    /* ── Load Profile ── */
    async function loadProfile() {
      try {
        const data = await apiSettings({
          action: 'get_profile'
        });
        if (!data.ok) return;
        const u = data.user;
        document.getElementById('first_name').value = u.firstName || '';
        document.getElementById('last_name').value = u.lastName || '';
        document.getElementById('email').value = u.email || '';
        document.getElementById('address').value = u.address || '';

        const currSel = document.getElementById('currency');
        if (currSel && u.currency) {
          [...currSel.options].forEach(o => {
            o.selected = o.value === u.currency;
          });
        }
        const unitSel = document.getElementById('units');
        if (unitSel && u.units) {
          [...unitSel.options].forEach(o => {
            o.selected = o.value === u.units;
          });
        }
      } catch (err) {
        console.warn('loadProfile:', err);
      }
    }

    /* ── Update Profile ── */
    async function updateProfile() {
      const btn = document.getElementById('update-profile-btn');
      btn.disabled = true;
      btn.textContent = 'Saving…';
      try {
        const data = await apiSettings({
          action: 'update_profile',
          first_name: document.getElementById('first_name').value,
          last_name: document.getElementById('last_name').value,
          email: document.getElementById('email').value,
          address: document.getElementById('address').value,
          currency: document.getElementById('currency').value,
          units: document.getElementById('units').value,
        });
        if (!data.ok) throw new Error(data.msg);
        $_SESSION_user = data.user;
        showToast('✅ Profile updated', 'ok');
      } catch (err) {
        showToast(`⚠ ${err.message}`, 'warn');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Update Profile';
      }
    }

    /* ── Change Password ── */
    async function changePassword() {
      const current = document.getElementById('current_password').value;
      const newPwd = document.getElementById('new_password').value;
      const confirm = document.getElementById('confirm_password').value;
      if (!current || !newPwd || !confirm) {
        showToast('Fill all password fields', 'warn');
        return;
      }
      if (newPwd !== confirm) {
        showToast('Passwords do not match', 'warn');
        return;
      }
      const btn = document.getElementById('change-password-btn');
      btn.disabled = true;
      btn.textContent = 'Changing…';
      try {
        const data = await apiSettings({
          action: 'change_password',
          current_password: current,
          new_password: newPwd,
          confirm_password: confirm,
        });
        if (!data.ok) throw new Error(data.msg);
        showToast('✅ Password changed', 'ok');
        ['current_password', 'new_password', 'confirm_password'].forEach(id => {
          document.getElementById(id).value = '';
        });
      } catch (err) {
        showToast(`⚠ ${err.message}`, 'warn');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Change Password';
      }
    }

    /* ── Load Tariffs ── */
    async function loadTariffs() {
      try {
        const data = await apiSettings({
          action: 'get_tariffs'
        });
        if (!data.ok) return;
        const t = data.tariffs;
        const elec = t.electricity || {};
        const sv = (id, val) => {
          const el = document.getElementById(id);
          if (el && val !== undefined) el.value = val;
        };
        sv('rate_peak', elec.peak_rate ?? 0.28);
        sv('rate_offpeak', elec.offpeak_rate ?? 0.12);
        sv('peak_start', elec.peak_start ?? '18:00');
        sv('peak_end', elec.peak_end ?? '22:00');
        sv('rate_water', t.water?.rate ?? 0.005);
        sv('rate_gas', t.gas?.rate ?? 0.45);
        sv('solar_capacity', elec.solar_capacity ?? 4.5);
      } catch (err) {
        console.warn('loadTariffs:', err);
      }
    }

    /* ── Update Tariffs ── */
    async function updateTariffs() {
      const btn = document.getElementById('update-tariffs-btn');
      btn.disabled = true;
      btn.textContent = 'Saving…';
      try {
        const updates = [{
            resource_type: 'electricity',
            rate: document.getElementById('rate_peak').value,
            peak_rate: document.getElementById('rate_peak').value,
            offpeak_rate: document.getElementById('rate_offpeak').value,
            peak_start: document.getElementById('peak_start').value,
            peak_end: document.getElementById('peak_end').value,
            solar_capacity: document.getElementById('solar_capacity').value
          },
          {
            resource_type: 'water',
            rate: document.getElementById('rate_water').value
          },
          {
            resource_type: 'gas',
            rate: document.getElementById('rate_gas').value
          },
        ];
        let allOk = true;
        for (const u of updates) {
          const d = await apiSettings({
            action: 'set_tariff',
            ...u
          });
          if (!d.ok) allOk = false;
        }
        showToast(allOk ? '✅ Tariffs updated' : '⚠ Some tariffs failed', allOk ? 'ok' : 'warn');
      } catch (err) {
        showToast(`⚠ ${err.message}`, 'warn');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Update Tariffs';
      }
    }

    /* ── Load Users ── */
    function roleBadge(role) {
      const map = {
        owner: 'badge--ok',
        admin: 'badge--warn',
        tenant: 'badge--info',
        guest: 'badge--neutral'
      };
      return `<span class="badge ${map[role]||'badge--neutral'}">${role||'unknown'}</span>`;
    }

    function permLabel(role) {
      return {
        owner: 'Full Access',
        admin: 'Full Access',
        tenant: 'View + Control',
        guest: 'View Only'
      } [role] || 'View Only';
    }

    async function loadUsers() {
      const tbody = document.getElementById('users-table-body');
      try {
        const res = await fetch('/AOT/api/users.php?action=list');
        const data = await res.json();
        if (!data.ok) throw new Error(data.msg);
        tbody.innerHTML = data.users.map(u => {
          const name = `${u.firstName||''} ${u.lastName||''}`.trim() || 'Unknown';
          const isMe = u.id == AOT_USER?.id;
          const statusCls = (u.status || 'active') === 'active' ? 'badge--ok' : 'badge--neutral';
          return `
            <tr>
              <td><b>${name}</b>${isMe?' <span class="badge badge--info">You</span>':''}<div class="text-xs text-muted">${u.email||'—'}</div></td>
              <td>
                <select class="form-select" style="width:auto;padding:4px 8px;font-size:.78rem"
                  onchange="updateRole(${u.id},this.value)" ${isMe?'disabled':''}>
                  <option value="owner"  ${u.role==='owner' ?'selected':''}>Owner</option>
                  <option value="tenant" ${u.role==='tenant'?'selected':''}>Tenant</option>
                  <option value="guest"  ${u.role==='guest' ?'selected':''}>Guest</option>
                </select>
              </td>
              <td class="text-muted text-xs">${permLabel(u.role)}</td>
              <td><span class="badge ${statusCls}">${u.status||'active'}</span></td>
              <td>${!isMe?`<button class="btn btn--outline btn--sm" onclick="removeUser(${u.id})">Remove</button>`:'—'}</td>
            </tr>`;
        }).join('');
      } catch (err) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-muted" style="text-align:center;padding:var(--sp-lg)">⚠ ${err.message}</td></tr>`;
      }
    }

    async function updateRole(id, role) {
      try {
        const fd = new FormData();
        fd.append('action', 'update_role');
        fd.append('id', id);
        fd.append('role', role);
        const data = await (await fetch('/AOT/api/users.php', {
          method: 'POST',
          body: fd
        })).json();
        if (!data.ok) throw new Error(data.msg);
        showToast(`✅ Role updated to ${role}`, 'ok');
        loadUsers();
      } catch (err) {
        showToast(`⚠ ${err.message}`, 'warn');
      }
    }

    async function removeUser(id) {
      if (!confirm('Remove this user?')) return;
      try {
        const fd = new FormData();
        fd.append('action', 'update_role');
        fd.append('id', id);
        fd.append('role', 'guest');
        const data = await (await fetch('/AOT/api/users.php', {
          method: 'POST',
          body: fd
        })).json();
        if (!data.ok) throw new Error(data.msg);
        showToast('User removed', 'ok');
        loadUsers();
      } catch (err) {
        showToast(`⚠ ${err.message}`, 'warn');
      }
    }

    /* ── Load System Settings ── */
    async function loadSystemSettings() {
      try {
        const data = await apiSettings({
          action: 'get_profile'
        });
        if (!data.ok) return;
        const u = data.user;
        const simEl = document.getElementById('sim_speed');
        if (simEl && u.sim_speed)[...simEl.options].forEach(o => {
          o.selected = o.value == u.sim_speed;
        });
        const retEl = document.getElementById('retention');
        if (retEl && u.retention)[...retEl.options].forEach(o => {
          o.selected = o.value == u.retention;
        });
        const bench = document.getElementById('toggle_benchmarking');
        if (bench) bench.checked = !!u.toggle_benchmarking;
        const weather = document.getElementById('toggle_weather');
        if (weather) weather.checked = !!u.toggle_weather;
        const audit = document.getElementById('toggle_audit');
        if (audit) audit.checked = !!u.toggle_audit;
      } catch (err) {
        console.warn('loadSystemSettings:', err);
      }
    }

    /* ── Save System Settings ── */
    async function saveSystemSettings() {
      const btn = document.getElementById('save-system-btn');
      btn.disabled = true;
      btn.textContent = 'Saving…';
      try {
        const data = await apiSettings({
          action: 'system_settings',
          sim_speed: document.getElementById('sim_speed').value,
          retention: document.getElementById('retention').value,
          toggle_benchmarking: document.getElementById('toggle_benchmarking').checked ? 1 : 0,
          toggle_weather: document.getElementById('toggle_weather').checked ? 1 : 0,
          toggle_audit: document.getElementById('toggle_audit').checked ? 1 : 0,
        });
        if (!data.ok) throw new Error(data.msg);
        showToast('✅ System settings saved', 'ok');
      } catch (err) {
        showToast(`⚠ ${err.message}`, 'warn');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Save System Settings';
      }
    }

    /* ── Danger Zone ── */
    async function purgeRawData() {
      if (!confirm('⚠ Delete all raw sensor readings?')) return;
      try {
        const res = await fetch('/AOT/api/settings.php?action=purge_raw');
        const data = await res.json();
        if (!data.ok) throw new Error(data.msg);
        showToast(`🗑 Purged ${data.purged} records`, 'ok');
      } catch (err) {
        showToast(`⚠ ${err.message}`, 'warn');
      }
    }

    async function deleteAccount() {
      if (!confirm('⚠ Permanently delete your account?')) return;
      if (!confirm('Are you absolutely sure?')) return;
      try {
        const fd = new FormData();
        fd.append('action', 'delete_account');
        const data = await (await fetch('/AOT/api/users.php', {
          method: 'POST',
          body: fd
        })).json();
        if (!data.ok) throw new Error(data.msg);
        window.location.href = '/AOT/login.php';
      } catch (err) {
        showToast(`⚠ ${err.message}`, 'warn');
      }
    }

    /* ── Boot ── */
    document.addEventListener('DOMContentLoaded', () => {

      document.getElementById('logout-btn')?.addEventListener('click', () => {
        if (confirm('Sign out?')) Auth.logout();
      });

      document.getElementById('update-profile-btn')?.addEventListener('click', updateProfile);
      document.getElementById('change-password-btn')?.addEventListener('click', changePassword);
      document.getElementById('update-tariffs-btn')?.addEventListener('click', updateTariffs);
      document.getElementById('save-system-btn')?.addEventListener('click', saveSystemSettings);
      document.getElementById('purge-data-btn')?.addEventListener('click', purgeRawData);
      document.getElementById('delete-account-btn')?.addEventListener('click', deleteAccount);
      document.getElementById('reset-sim-btn')?.addEventListener('click', () => {
        if (confirm('Reset simulation data?')) showToast('🔄 Simulation reset', 'ok');
      });
      document.getElementById('save-all-btn')?.addEventListener('click', () => {
        updateProfile();
        updateTariffs();
        saveSystemSettings();
      });
      document.getElementById('invite-user-btn')?.addEventListener('click', () => {
        const email = prompt('Enter email to invite:');
        if (!email) return;
        const role = prompt('Role (tenant / guest):') || 'guest';
        apiSettings({
            action: 'invite_user',
            email,
            role
          })
          .then(d => d.ok ? showToast(`📨 Invite sent to ${email}`, 'ok') : showToast(d.msg, 'warn'))
          .catch(err => showToast(`⚠ ${err.message}`, 'warn'));
      });

      loadProfile();
      loadTariffs();
      loadUsers();
      loadSystemSettings();
    });

    let homeCodeVisible = false;
    const REAL_HOME_CODE = '<?php
                            // Get home code from DB
                            $homeStmt = getDB()->prepare("SELECT home_code FROM homes WHERE owner_id = ?");
                            $homeStmt->bind_param("i", $user["id"]);
                            $homeStmt->execute();
                            $homeRow = $homeStmt->get_result()->fetch_assoc();
                            $homeStmt->close();
                            echo htmlspecialchars($homeRow["home_code"] ?? "");
                            ?>';

    function toggleHomeCode() {
      homeCodeVisible = !homeCodeVisible;
      document.getElementById('home-code-display').textContent =
        homeCodeVisible ? REAL_HOME_CODE : '••••••••';
      event.target.textContent = homeCodeVisible ? '🙈 Hide' : '👁 Show';
    }

    function copyHomeCode() {
      navigator.clipboard.writeText(REAL_HOME_CODE)
        .then(() => showToast('✅ Home code copied!', 'ok'))
        .catch(() => showToast('Could not copy', 'warn'));
    }
  </script>
</body>

</html>