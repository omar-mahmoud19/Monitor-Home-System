<?php
require_once __DIR__ . '/../api/session.php';
checkRole(['owner', 'tenant']);
$user = getCurrentUser();
if ($user) unset($user['password']);
?>
<script>
  const AOT_USER = <?php echo json_encode($user ?? null); ?>;
</script>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Goals &amp; Budget — AOT Homes</title>
  <link rel="stylesheet" href="/AOT/assets/css/style.css" />
  <script src="/AOT/assets/js/main.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
      <div class="sidebar__avatar" id="sidebar-avatar">AO</div>
      <div>
        <div class="sidebar__user-name" id="sidebar-name">Home Owner</div>
        <div class="sidebar__user-role" id="sidebar-role">owner</div>
      </div>
      <button class="sidebar__logout" id="logout-btn" title="Sign Out" style="margin-left:auto;background:none;border:none;cursor:pointer;font-size:1rem;color:var(--text-3)">✕</button>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <h1 class="topbar__title">Goals &amp; Budget</h1>
      <span class="topbar__meta" id="today-date"></span>
      <span class="topbar__meta" id="live-clock"></span>
      <button class="topbar__btn primary" id="new-goal-btn">+ Set New Goal</button>
    </header>

    <div class="content">

      <!-- Budget Alert Banner (injected by JS if overrun) -->
      <div id="budget-alert-banner"></div>

      <!-- Eco Points Banner -->
      <div class="card card--accent mb-lg" style="display:flex;align-items:center;gap:var(--sp-lg)">
        <div style="font-size:2.5rem">🌿</div>
        <div style="flex:1">
          <div class="font-head" style="font-size:1.1rem" id="eco-credits-banner">
            You've earned <span class="text-accent" id="eco-credits-value">— Eco-Credits</span> this month!
          </div>
          <div class="text-sm text-muted mt-md">Reduce water by 10% this week to unlock your next milestone badge.</div>
        </div>
        <div style="text-align:right">
          <div class="font-head" style="font-size:2rem;color:var(--clr-accent)" id="eco-rank">Rank —</div>
          <div class="text-xs text-muted">Neighborhood Leaderboard</div>
        </div>
      </div>

      <!-- Budget Gauges -->
      <div class="section-header">
        <div class="section-title">Monthly Budgets</div>
        <div class="text-sm text-muted" id="budget-load-status">Loading from API…</div>
      </div>

      <div class="grid-3 mb-lg" id="budget-gauges">
        <!-- Rendered by JS from api/goals.php?action=budget -->
        <div class="card" style="opacity:.4;pointer-events:none">
          <div class="flex-center gap-sm mb-md"><span style="font-size:1.3rem">⚡</span><span class="font-head">Electricity</span><span class="ml-auto badge badge--warn">—</span></div>
          <div style="font-size:2rem;font-family:var(--font-head);color:var(--clr-elec)">— <small style="font-size:.9rem;color:var(--clr-muted)">/ —</small></div>
          <div class="progress mt-md">
            <div class="progress__fill progress__fill--elec" style="width:0%"></div>
          </div>
          <div class="text-xs text-muted mt-md">Loading…</div>
        </div>
        <div class="card" style="opacity:.4;pointer-events:none">
          <div class="flex-center gap-sm mb-md"><span style="font-size:1.3rem">💧</span><span class="font-head">Water</span><span class="ml-auto badge badge--warn">—</span></div>
          <div style="font-size:2rem;font-family:var(--font-head);color:var(--clr-water)">— <small style="font-size:.9rem;color:var(--clr-muted)">/ —</small></div>
          <div class="progress mt-md">
            <div class="progress__fill" style="width:0%"></div>
          </div>
          <div class="text-xs text-muted mt-md">Loading…</div>
        </div>
        <div class="card" style="opacity:.4;pointer-events:none">
          <div class="flex-center gap-sm mb-md"><span style="font-size:1.3rem">🔥</span><span class="font-head">Gas</span><span class="ml-auto badge badge--warn">—</span></div>
          <div style="font-size:2rem;font-family:var(--font-head);color:var(--clr-gas)">— <small style="font-size:.9rem;color:var(--clr-muted)">/ —</small></div>
          <div class="progress mt-md">
            <div class="progress__fill progress__fill--gas" style="width:0%"></div>
          </div>
          <div class="text-xs text-muted mt-md">Loading…</div>
        </div>
      </div>

      <!-- Smart Budget Suggestions (injected after budget loads) -->
      <div id="smart-suggestions-container"></div>

      <div class="grid-2">

        <!-- Active Eco-Challenges -->
        <div class="card">
          <div class="section-header">
            <div class="section-title">🏆 Active Eco-Challenges</div>
          </div>
          <div id="eco-challenges-list" style="display:flex;flex-direction:column;gap:var(--sp-md)">
            <div class="text-sm text-muted">Loading challenges…</div>
          </div>
        </div>

        <!-- What-If Simulator -->
        <div class="card">
          <div class="section-header">
            <div>
              <div class="section-title">🔮 What-If Savings Simulator</div>
              <div class="section-sub">See how much you'd save by replacing an appliance</div>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Select Appliance to Replace</label>
            <select class="form-select" name="whatif_appliance">
              <option>Refrigerator (6 yrs old)</option>
              <option>HVAC Unit (3 yrs old)</option>
              <option>Washing Machine (8 yrs old)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Replace With</label>
            <select class="form-select" name="whatif_model">
              <option>Energy Star Model (A+++)</option>
              <option>Standard New Model (A+)</option>
            </select>
          </div>
          <button class="btn btn--primary" id="whatif-btn" style="width:100%;justify-content:center">Calculate Savings</button>

          <div class="card mt-md" style="background:var(--clr-surface-2)" id="whatif-result">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:var(--sp-md);text-align:center">
              <div>
                <div class="text-xs text-muted">Monthly Saving</div>
                <div class="font-head text-accent" style="font-size:1.4rem" id="whatif-monthly">$—</div>
              </div>
              <div>
                <div class="text-xs text-muted">Yearly Saving</div>
                <div class="font-head text-accent" style="font-size:1.4rem" id="whatif-yearly">$—</div>
              </div>
              <div>
                <div class="text-xs text-muted">CO₂ Saved</div>
                <div class="font-head text-accent" style="font-size:1.4rem" id="whatif-co2">— kg</div>
              </div>
            </div>
            <div class="text-xs text-muted mt-md" style="text-align:center" id="whatif-payback">Run the calculator above to see results.</div>
          </div>
        </div>

      </div><!-- /grid-2 -->

      <!-- Peak-Shaving Advisor (injected by JS) -->
      <div id="peak-shaving-container"></div>

      <!-- Room Budgets (injected by JS) -->
      <div id="room-budgets-container"></div>

      <!-- Milestones -->
      <div class="card mt-lg">
        <div class="section-header">
          <div class="section-title">🏅 Sustainability Milestones</div>
        </div>
        <div id="milestones-container" style="display:flex;gap:var(--sp-lg);flex-wrap:wrap">
          <div class="text-sm text-muted">Loading milestones…</div>
        </div>
      </div>

      <!-- Neighborhood Leaderboard (injected by JS) -->
      <div id="leaderboard-container"></div>

    </div><!-- /content -->
  </main>


  <script>
    /**
     * ─────────────────────────────────────────────────────────────
     *  goals.php — Client-side integration with api/goals.php
     *  All data is fetched from the real API endpoints.
     * ─────────────────────────────────────────────────────────────
     */

    /* ── Utility: POST to api/goals.php ── */
    async function apiGoals(body = {}) {
      const fd = new FormData();
      for (const [k, v] of Object.entries(body)) fd.append(k, v);
      const res = await fetch('/AOT/api/goals.php', {
        method: 'POST',
        body: fd
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    }

    /* ── Utility: GET with query params ── */
    async function apiGoalsGet(params = {}) {
      const qs = new URLSearchParams(params).toString();
      const res = await fetch(`/AOT/api/goals.php?${qs}`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    }

    /* ── Resource config ── */
    const RESOURCE_CONFIG = {
      electricity: {
        icon: '⚡',
        colorVar: 'var(--clr-elec)',
        fillClass: 'progress__fill--elec',
        unit: '$'
      },
      water: {
        icon: '💧',
        colorVar: 'var(--clr-water)',
        fillClass: 'progress__fill--danger',
        unit: '$'
      },
      gas: {
        icon: '🔥',
        colorVar: 'var(--clr-gas)',
        fillClass: 'progress__fill--gas',
        unit: '$'
      },
    };

    /* ── Resolve badge class from % ── */
    function badgeClass(pct) {
      if (pct >= 90) return 'badge--danger';
      if (pct >= 70) return 'badge--warn';
      return 'badge--ok';
    }

    /* ─────────────────────────────────────────────────────────
     *  FUNCTION 23/31 — Load budgets from api/goals.php?action=budget
     *  Renders gauges + injects smart suggestions + escalation alerts
     * ───────────────────────────────────────────────────────── */
    async function loadBudgets() {
      const statusEl = document.getElementById('budget-load-status');
      try {
        const data = await apiGoalsGet({
          action: 'budget'
        });
        if (!data.ok) throw new Error(data.msg || 'API error');

        const budgets = data.budget; // array from api
        statusEl.textContent = `Updated just now`;

        /* Build gauge cards */
        const container = document.getElementById('budget-gauges');
        container.innerHTML = '';

        // Normalise resource_type keys → display order
        const resourceOrder = ['electricity', 'water', 'gas'];
        const budgetMap = {};
        (budgets || []).forEach(b => {
          budgetMap[b.resource_type] = b;
        });

        resourceOrder.forEach(res => {
          const b = budgetMap[res];
          const cfg = RESOURCE_CONFIG[res] || {
            icon: '📊',
            colorVar: 'var(--clr-accent)',
            fillClass: '',
            unit: '$'
          };

          if (!b) {
            // No goal set for this resource yet
            container.innerHTML += `
          <div class="card">
            <div class="flex-center gap-sm mb-md">
              <span style="font-size:1.3rem">${cfg.icon}</span>
              <span class="font-head">${res.charAt(0).toUpperCase()+res.slice(1)}</span>
              <span class="ml-auto badge badge--neutral">No goal</span>
            </div>
            <div class="text-sm text-muted">No budget set for this resource yet.</div>
            <div class="divider"></div>
            <div class="form-group" style="margin:0">
              <label class="form-label">Set Budget (${cfg.unit})</label>
              <div style="display:flex;gap:var(--sp-sm)">
                <input class="form-input" type="number" min="1" placeholder="e.g. 50" data-res="${res}" id="budget-input-${res}" />
                <button class="btn btn--primary btn--sm" onclick="createBudget('${res}')">Create</button>
              </div>
            </div>
          </div>`;
            return;
          }

          const pct = Math.round(b.progress_pct || 0);
          const current = parseFloat(b.current).toFixed(2);
          const target = parseFloat(b.target).toFixed(2);
          const remain = Math.max(0, target - current).toFixed(2);
          const isDanger = pct >= 90;

          container.innerHTML += `
        <div class="card ${isDanger ? 'card--danger' : ''}">
          <div class="flex-center gap-sm mb-md">
            <span style="font-size:1.3rem">${cfg.icon}</span>
            <span class="font-head">${res.charAt(0).toUpperCase()+res.slice(1)}</span>
            <span class="ml-auto badge ${badgeClass(pct)}">${pct}%</span>
          </div>
          <div style="font-size:2rem;font-family:var(--font-head);color:${cfg.colorVar}">
            ${cfg.unit}${current}
            <small style="font-size:.9rem;color:var(--clr-muted)">/ ${cfg.unit}${target}</small>
          </div>
          <div class="progress mt-md">
            <div class="progress__fill ${isDanger ? 'progress__fill--danger' : cfg.fillClass}" style="width:${pct}%"></div>
          </div>
          <div class="text-xs ${isDanger ? 'text-danger' : 'text-muted'} mt-md">
            ${isDanger ? '⚠ Almost over budget!' : `${cfg.unit}${remain} remaining`}
          </div>
          <div class="divider"></div>
          <div class="form-group" style="margin:0">
            <label class="form-label">Adjust Budget (${cfg.unit})</label>
            <div style="display:flex;gap:var(--sp-sm)">
              <input class="form-input" type="number" value="${target}" data-goal-id="${b.id || ''}" data-res="${res}" id="budget-input-${res}" />
              <button class="btn btn--primary btn--sm" onclick="updateBudget('${res}', '${b.id || ''}')">Save</button>
            </div>
          </div>
        </div>`;
        });

        /* FUNCTION 31 — Budget Escalation Alerts */
        renderEscalationAlerts(budgetMap);

        /* FUNCTION 23 — Smart Budget Suggestions */
        renderSmartSuggestions(budgetMap);

      } catch (err) {
        statusEl.textContent = `⚠ Could not load budgets`;
        console.error('loadBudgets:', err);
      }
    }

    /* ── Create a new budget goal ── */
    async function createBudget(resource) {
      const input = document.getElementById(`budget-input-${resource}`);
      const val = parseFloat(input?.value);
      if (!val || val <= 0) {
        showToast('Enter a valid budget amount', 'warn');
        return;
      }

      try {
        const data = await apiGoals({
          action: 'create',
          resource_type: resource,
          target_value: val,
          period: 'monthly',
          unit: '$',
        });
        if (!data.ok) throw new Error(data.msg);
        showToast(`✅ ${resource} budget created: $${val}`, 'ok');
        loadBudgets();
      } catch (err) {
        showToast(`Failed to create budget: ${err.message}`, 'warn');
      }
    }

    /* ── Update an existing budget goal ── */
    async function updateBudget(resource, goalId) {
      const input = document.getElementById(`budget-input-${resource}`);
      const val = parseFloat(input?.value);
      if (!val || val <= 0) {
        showToast('Enter a valid budget amount', 'warn');
        return;
      }

      try {
        if (goalId) {
          const data = await apiGoals({
            action: 'update',
            id: goalId,
            target_value: val
          });
          if (!data.ok) throw new Error(data.msg);
        } else {
          await createBudget(resource);
          return;
        }
        showToast(`✅ ${resource} budget updated to $${val}`, 'ok');
        loadBudgets();
      } catch (err) {
        showToast(`Failed to update budget: ${err.message}`, 'warn');
      }
    }

    /* ── FUNCTION 31: Escalation alerts ── */
    function renderEscalationAlerts(budgetMap) {
      const banner = document.getElementById('budget-alert-banner');
      banner.innerHTML = '';
      Object.entries(budgetMap).forEach(([res, b]) => {
        const pct = Math.round(b.progress_pct || 0);
        if (pct < 70) return;
        const isCritical = pct >= 95;
        banner.innerHTML += `
      <div class="alert alert--${isCritical ? 'danger' : 'warn'} mb-md">
        <span class="alert__icon">${isCritical ? '🚨' : '⚠️'}</span>
        <div>
          <div class="alert__title">${isCritical ? 'CRITICAL' : 'WARNING'}</div>
          <div class="alert__msg">${res.charAt(0).toUpperCase()+res.slice(1)} budget is at ${pct}%${isCritical ? ' — over-budget risk!' : '.'}</div>
        </div>
        <button class="btn btn--sm btn--outline" style="margin-left:auto" onclick="this.closest('.alert').remove()">Dismiss</button>
      </div>`;
      });
    }

    /* ── FUNCTION 23: Smart suggestions ── */
    function renderSmartSuggestions(budgetMap) {
      const container = document.getElementById('smart-suggestions-container');
      const suggestions = [];
      Object.entries(budgetMap).forEach(([res, b]) => {
        const pct = Math.round(b.progress_pct || 0);
        const current = parseFloat(b.current);
        const target = parseFloat(b.target);
        // If they are consistently under-spending, suggest lowering; over-spending, suggest raising
        if (pct > 90) {
          const suggested = Math.ceil(current * 1.15);
          suggestions.push({
            resource: res,
            current: target.toFixed(0),
            suggested,
            id: b.id || ''
          });
        } else if (pct < 50 && target > 10) {
          const suggested = Math.floor(current * 1.1);
          suggestions.push({
            resource: res,
            current: target.toFixed(0),
            suggested,
            id: b.id || ''
          });
        }
      });
      if (!suggestions.length) {
        container.innerHTML = '';
        return;
      }
      container.innerHTML = `
    <div class="card mb-lg">
      <div class="section-header">
        <div class="section-title">💡 Smart Budget Suggestions</div>
        <div class="section-sub">Based on your current spending pace</div>
      </div>
      <div style="display:flex;flex-direction:column;gap:var(--sp-sm)">
        ${suggestions.map(s => `
          <div class="flex-center gap-sm" style="padding:10px 14px;background:var(--clr-surface-2);border-radius:var(--radius-sm)">
            <span class="text-sm">💰 ${s.resource}: current <b>$${s.current}</b> → suggested <b class="text-accent">$${s.suggested}</b></span>
            <button class="btn btn--sm btn--primary ml-auto"
              onclick="updateBudget('${s.resource}','${s.id}');document.getElementById('budget-input-${s.resource}').value='${s.suggested}'">Apply</button>
          </div>`).join('')}
      </div>
    </div>`;
    }

    /* ─────────────────────────────────────────────────────────
     *  FUNCTION 24 — Eco-Challenges: render from JS module
     *  (EcoChallenge.getActive() from main.js)
     * ───────────────────────────────────────────────────────── */
    function loadEcoChallenges() {
      try {
        const activeChallenges = EcoChallenge.getActive();
        const list = document.getElementById('eco-challenges-list');
        if (!activeChallenges?.length) {
          list.innerHTML = `<div class="text-sm text-muted">No active challenges. Start one below!</div>`;
        } else {
          list.innerHTML = activeChallenges.map(c => {
            const pct = Math.round(c.progress || 0);
            const daysLeft = Math.max(0, c.days - Math.floor((Date.now() - new Date(c.startedAt)) / 86400000));
            const stateClass = c.state === 'completed' ? 'badge--ok' : c.state === 'failed' ? 'badge--danger' : 'badge--warn';
            const stateLabel = c.state === 'completed' ? '✅ Done' : c.state === 'failed' ? '❌ Failed' : `${daysLeft}d left`;
            const resIcon = c.resource === 'water' ? '💧' : c.resource === 'gas' ? '🔥' : '⚡';
            return `
          <div class="card" style="background:var(--clr-surface-2);padding:var(--sp-md)">
            <div class="flex-center gap-sm mb-md">
              <span>${resIcon}</span>
              <b>${c.name}</b>
              <span class="badge ${stateClass} ml-auto">${stateLabel}</span>
            </div>
            <div class="text-xs text-muted mb-md">${c.desc || ''}</div>
            <div class="progress"><div class="progress__fill" style="width:${pct}%"></div></div>
            <div class="flex-center gap-sm mt-md">
              <span class="text-xs text-muted">${pct}% complete</span>
              <span class="ml-auto text-xs text-accent">+${c.reward} Eco-Credits on completion</span>
            </div>
          </div>`;
          }).join('');
        }

        list.innerHTML += `
      <button class="btn btn--outline btn--sm" style="width:100%;justify-content:center;margin-top:var(--sp-sm)"
        onclick="EcoChallenge.start('water10');showToast('Water Saver Challenge started! 💧','ok');location.reload()">
        + Start New Challenge
      </button>`;
      } catch (err) {
        console.warn('EcoChallenge not available:', err);
      }
    }

    /* ─────────────────────────────────────────────────────────
     *  FUNCTION 25 — Eco-Credits Banner
     *  (RewardSystem.getCredits() + getLeaderboard() from main.js)
     * ───────────────────────────────────────────────────────── */
    function loadEcoCredits() {
      try {
        const credits = RewardSystem.getCredits();
        const leaderboard = getLeaderboard();
        const rank = leaderboard.findIndex(e => e.isMe) + 1 || '—';
        document.getElementById('eco-credits-value').textContent = `${credits.toLocaleString()} Eco-Credits`;
        document.getElementById('eco-rank').textContent = `Rank #${rank}`;
      } catch (err) {
        console.warn('RewardSystem not available:', err);
      }
    }

    /* ─────────────────────────────────────────────────────────
     *  FUNCTION 26 — What-If Simulator
     *  Calls whatIfSimulator() from main.js
     * ───────────────────────────────────────────────────────── */
    function initWhatIfSimulator() {
      document.getElementById('whatif-btn')?.addEventListener('click', () => {
        const appEl = document.querySelector('select[name="whatif_appliance"]');
        const modelEl = document.querySelector('select[name="whatif_model"]');
        const effMap = {
          'Energy Star Model (A+++)': 40,
          'Standard New Model (A+)': 20
        };
        const watts = {
          'Refrigerator (6 yrs old)': 150,
          'HVAC Unit (3 yrs old)': 1200,
          'Washing Machine (8 yrs old)': 2000
        };
        const ageMap = {
          'Refrigerator (6 yrs old)': 6,
          'HVAC Unit (3 yrs old)': 3,
          'Washing Machine (8 yrs old)': 8
        };

        const sel = appEl?.value || 'Refrigerator (6 yrs old)';
        const eff = effMap[modelEl?.value] || 40;
        const mock = {
          name: sel,
          ratedWatts: watts[sel] || 150,
          ageYears: ageMap[sel] || 5
        };

        try {
          const result = whatIfSimulator(mock, eff);
          document.getElementById('whatif-monthly').textContent = `$${result.monthlySaving}`;
          document.getElementById('whatif-yearly').textContent = `$${result.yearlySaving}`;
          document.getElementById('whatif-co2').textContent = `${result.co2Saved} kg`;
          document.getElementById('whatif-payback').textContent = `Payback period: ~${result.paybackYears} years`;
          showToast(`💡 Saving $${result.monthlySaving}/mo by upgrading ${sel.split(' (')[0]}`, 'ok');
        } catch (err) {
          showToast('Simulator not available', 'warn');
        }
      });
    }

    /* ─────────────────────────────────────────────────────────
     *  FUNCTION 27 — Room Budgets
     *  (getRoomBudgets() from main.js)
     * ───────────────────────────────────────────────────────── */
    function loadRoomBudgets() {
      try {
        const roomBudgets = getRoomBudgets();
        const container = document.getElementById('room-budgets-container');
        container.innerHTML = `
      <div class="card mt-lg">
        <div class="section-header">
          <div class="section-title">🏠 Room-by-Room Budget Allocation</div>
          <div class="section-sub">Departmental / Room Budgeting</div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:var(--sp-md)">
          ${roomBudgets.map(r => {
            const pct = Math.round((r.spent / r.budget) * 100);
            const cls = pct >= 90 ? 'danger' : pct >= 70 ? 'warn' : 'ok';
            return `
              <div style="padding:var(--sp-md);background:var(--clr-surface-2);border-radius:var(--radius-sm)">
                <div class="flex-center gap-sm mb-md">
                  <span>${r.icon}</span>
                  <span class="text-sm font-head">${r.room}</span>
                  <span class="badge badge--${cls} ml-auto">${pct}%</span>
                </div>
                <div style="font-size:1.2rem;font-family:var(--font-head)">$${r.spent} <small style="font-size:.75rem;color:var(--clr-muted)">/ $${r.budget}</small></div>
                <div class="progress mt-md">
                  <div class="progress__fill ${cls === 'danger' ? 'progress__fill--danger' : ''}" style="width:${pct}%;background:var(--clr-${cls === 'ok' ? 'ok' : cls})"></div>
                </div>
              </div>`;
          }).join('')}
        </div>
      </div>`;
      } catch (err) {
        console.warn('getRoomBudgets not available:', err);
      }
    }

    /* ─────────────────────────────────────────────────────────
     *  FUNCTION 28 — Peak-Shaving Advisor
     *  (peakShavingAdvice() from main.js)
     * ───────────────────────────────────────────────────────── */
    function loadPeakShaving() {
      try {
        const tariff = getSettings().tariff;
        const advice = peakShavingAdvice(tariff);
        const container = document.getElementById('peak-shaving-container');
        container.innerHTML = `
      <div class="card mt-lg">
        <div class="section-header">
          <div class="section-title">⏰ Peak-Shaving Advisor</div>
          <div class="section-sub">Best times to run heavy appliances</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:var(--sp-sm)">
          ${advice.map(a => `
            <div class="flex-center gap-sm" style="padding:10px 14px;background:var(--clr-surface-2);border-radius:var(--radius-sm)">
              <span style="font-size:1.1rem">${a.icon}</span>
              <div style="flex:1">
                <div class="text-sm font-head">${a.appliance}</div>
                <div class="text-xs text-muted">${a.suggestion}</div>
              </div>
              <span class="badge badge--ok">Save $${a.saving}/mo</span>
            </div>`).join('')}
        </div>
      </div>`;
      } catch (err) {
        console.warn('peakShavingAdvice not available:', err);
      }
    }

    /* ─────────────────────────────────────────────────────────
     *  FUNCTION 29 — Sustainability Milestones
     *  (checkMilestones() from main.js)
     *  Also calls api/goals.php?action=list to get real CO₂ total
     * ───────────────────────────────────────────────────────── */
    async function loadMilestones() {
      let totalCO2 = 247; // fallback
      try {
        const data = await apiGoalsGet({
          action: 'list'
        });
        if (data.ok && data.goals) {
          const co2Goal = data.goals.find(g => g.resource_type === 'co2');
          if (co2Goal) totalCO2 = parseFloat(co2Goal.current_value) || totalCO2;
        }
      } catch (e) {
        /* use fallback */
      }

      try {
        const milestones = checkMilestones(totalCO2);
        const container = document.getElementById('milestones-container');
        container.innerHTML = milestones.map(m => `
      <div style="text-align:center;flex:1;min-width:120px;${m.locked ? 'opacity:0.4' : ''}">
        <div style="font-size:2.5rem">${m.icon}</div>
        <div class="font-head ${m.achieved ? 'text-accent' : ''}">${m.target}</div>
        <div class="text-xs text-muted">${m.label}</div>
        <div class="badge ${m.achieved ? 'badge--ok' : m.locked ? 'badge--neutral' : 'badge--warn'} mt-md">
          ${m.achieved ? 'Achieved' : m.locked ? 'Locked' : `${Math.round((totalCO2/parseFloat(m.target))*100)}%`}
        </div>
      </div>`).join('');
      } catch (err) {
        console.warn('checkMilestones not available:', err);
      }
    }

    /* ─────────────────────────────────────────────────────────
     *  FUNCTION 30 — Neighborhood Leaderboard
     *  (getLeaderboard() from main.js)
     * ───────────────────────────────────────────────────────── */
    function loadLeaderboard() {
      try {
        const leaderboard = getLeaderboard();
        const container = document.getElementById('leaderboard-container');
        container.innerHTML = `
      <div class="card mt-lg">
        <div class="section-header">
          <div class="section-title">🏆 Neighborhood Eco-Leaderboard</div>
          <div class="section-sub">Peer Comparison</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:var(--sp-sm)">
          ${leaderboard.slice(0, 6).map((e, i) => `
            <div class="flex-center gap-sm" style="padding:9px 14px;background:${e.isMe ? 'rgba(129,140,248,.1)' : 'var(--clr-surface-2)'};border-radius:var(--radius-sm);${e.isMe ? 'border:1px solid rgba(129,140,248,.3)' : ''}">
              <span class="font-head" style="width:24px;color:${i < 3 ? ['#fbbf24','#9ca3af','#b45309'][i] : 'var(--clr-muted)'}">#${i+1}</span>
              <span class="text-sm flex-1">${e.name}${e.isMe ? ' <b style="color:var(--clr-accent)">(You)</b>' : ''}</span>
              <span class="badge badge--${i === 0 ? 'ok' : 'neutral'}">${e.score} pts</span>
            </div>`).join('')}
        </div>
      </div>`;
      } catch (err) {
        console.warn('getLeaderboard not available:', err);
      }
    }

    /* ─────────────────────────────────────────────────────────
     *  FUNCTION: "Set New Goal" button → POST to api/goals.php
     * ───────────────────────────────────────────────────────── */
    function initNewGoalModal() {
      document.getElementById('new-goal-btn')?.addEventListener('click', async () => {
        const resource = prompt('Resource (electricity / water / gas):');
        if (!resource) return;
        const target = parseFloat(prompt(`Monthly budget target in $ for ${resource}:`));
        if (!target || isNaN(target)) {
          showToast('Invalid amount', 'warn');
          return;
        }

        try {
          const data = await apiGoals({
            action: 'create',
            resource_type: resource.toLowerCase().trim(),
            target_value: target,
            period: 'monthly',
            unit: '$',
          });
          if (!data.ok) throw new Error(data.msg);
          showToast(`🎯 Goal created for ${resource}: $${target}`, 'ok');
          loadBudgets();
        } catch (err) {
          showToast(`Failed: ${err.message}`, 'warn');
        }
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

      /* Wire up all sections */
      initNewGoalModal();
      initWhatIfSimulator();

      loadEcoCredits(); // Function 25 — banner
      loadBudgets(); // Function 23 + 31 — budget gauges, suggestions, alerts
      loadEcoChallenges(); // Function 24 — challenges
      loadMilestones(); // Function 29 — milestones (fetches API for CO₂)
      loadPeakShaving(); // Function 28 — peak-shaving advisor
      loadRoomBudgets(); // Function 27 — room budgets
      loadLeaderboard(); // Function 30 — leaderboard
    });
  </script>
</body>

</html>