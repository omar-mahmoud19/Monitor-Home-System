/* ═══════════════════════════════════════════════════════════════
   AOT Homes — Energy Monitor  |  main.js  v3.0
   CS251 Software Engineering — Capital University
   ─────────────────────────────────────────────────────────────
   All 42 project functions are implemented here as JS simulations.
   PHP integration points are marked with: // PHP: ...
   ─────────────────────────────────────────────────────────────

   HOW TO CONNECT TO PHP (XAMPP):
   1. Rename .html files to .php
   2. Add at top of each page:
        // session handled server-side
   3. Replace fetch('...') placeholders with real endpoints
   4. Each function below has a // PHP: comment showing the endpoint
═══════════════════════════════════════════════════════════════ */

"use strict";

/* ═══════════════════════════════════════════════════════════
   USER SESSION — localStorage simulation
   PHP: Replace with $_SESSION checks
═══════════════════════════════════════════════════════════ */
const Auth = {
  // Check if user is logged in
  check() {
    const u = this.getUser();
    if (
      !u &&
      !window.location.pathname.includes("login") &&
      !window.location.pathname.includes("register")
    ) {
      window.location.href = "login.php";
    }
    return u;
  },

  // Register new user
  register(data) {
    const users = JSON.parse(localStorage.getItem("aot_users") || "[]");
    if (users.find((u) => u.email === data.email))
      return { ok: false, msg: "Email already registered." };
    const user = {
      id: Date.now(),
      name: data.firstName + " " + data.lastName,
      firstName: data.firstName,
      lastName: data.lastName,
      email: data.email,
      homeName: data.homeName,
      role: data.role,
      avatar: (data.firstName[0] + data.lastName[0]).toUpperCase(),
      createdAt: new Date().toISOString(),
      currency: "USD",
      units: "metric",
    };
    users.push(user);
    localStorage.setItem("aot_users", JSON.stringify(users));
    return { ok: true, user };
  },

  // Login user
  login(email, password) {
    const users = JSON.parse(localStorage.getItem("aot_users") || "[]");
    const user = users.find((u) => u.email === email);
    if (!user) return { ok: false, msg: "No account found with that email." };
    localStorage.setItem("aot_session", JSON.stringify(user));
    fetch("api/login_session.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ userId: user.id }),
    });
    return { ok: true, user };
  },

  // Logout user and redirect to login page
  logout() {
    localStorage.removeItem("aot_session");
    window.location.href = "/AOT/api/logout.php";
  },

  getUser() {
    try {
      const u = JSON.parse(localStorage.getItem("aot_session"));
      return u || null; // Return user or null, no demo fallback
    } catch {
      return null;
    }
  },

  updateUser(data) {
    const user = { ...this.getUser(), ...data };
    localStorage.setItem("aot_session", JSON.stringify(user));
    const users = JSON.parse(localStorage.getItem("aot_users") || "[]");
    const idx = users.findIndex((u) => u.id === user.id);
    if (idx !== -1) {
      users[idx] = user;
      localStorage.setItem("aot_users", JSON.stringify(users));
    }
    return user;
  },
};
/* ═══════════════════════════════════════════════════════════
   POPULATE SIDEBAR with logged-in user info
═══════════════════════════════════════════════════════════ */
function populateSidebar() {
  const user = Auth.getUser();
  if (!user) return;
  const avatarEl = document.getElementById("sidebar-avatar");
  const nameEl = document.getElementById("sidebar-name");
  const roleEl = document.getElementById("sidebar-role");
  if (avatarEl) avatarEl.textContent = user.avatar || "AO";
  if (nameEl) nameEl.textContent = user.name || "Home Owner";
  if (roleEl) roleEl.textContent = user.role || "owner";
}

/* ═══════════════════════════════════════════════════════════
   ██████  FUNCTION GROUP A — Resource Analytics (11 Functions)
═══════════════════════════════════════════════════════════ */

/**
 * FUNCTION 1 — Tiered Tariff Calculation Engine
 * Calculates electricity cost using Time-of-Use (Peak/Off-Peak) rates.
 * PHP: api/tariff.php — reads rates from DB, returns cost JSON
 */
function calcTariffCost(kWh, hour, rates = null) {
  const r = rates || getSettings().tariff;
  const isPeak = hour >= r.peakStart && hour < r.peakEnd;
  const rate = isPeak ? r.peak : r.offPeak;
  return { cost: +(kWh * rate).toFixed(4), rate, isPeak };
}

/**
 * FUNCTION 2 — Carbon Footprint Estimator
 * Converts kWh, gas m³, water litres → CO2 kg
 * PHP: api/carbon.php
 */
function estimateCarbonFootprint(kWh, gasM3, waterL) {
  // Standard emission factors (kg CO2 per unit)
  const factors = { elec: 0.233, gas: 2.04, water: 0.298 / 1000 };
  const elecCO2 = kWh * factors.elec;
  const gasCO2 = gasM3 * factors.gas;
  const waterCO2 = waterL * factors.water;
  const total = +(elecCO2 + gasCO2 + waterCO2).toFixed(2);
  return {
    total,
    elecCO2: +elecCO2.toFixed(3),
    gasCO2: +gasCO2.toFixed(3),
    waterCO2: +waterCO2.toFixed(3),
  };
}

/**
 * FUNCTION 3 — Predictive Billing Algorithm
 * Uses last 7 days of usage to project monthly bill
 * PHP: api/billing.php?range=7
 */
function predictMonthlyBill(last7DaysCosts) {
  const avg = last7DaysCosts.reduce((a, b) => a + b, 0) / last7DaysCosts.length;
  const projected = +(avg * 30).toFixed(2);
  const daysLeft = 30 - new Date().getDate();
  const soFar = +(avg * new Date().getDate()).toFixed(2);
  return { projected, soFar, daysLeft, dailyAvg: +avg.toFixed(2) };
}

/**
 * FUNCTION 4 — Resource Baselines Generator
 * Establishes "normal" usage per day-of-week
 * PHP: api/baseline.php
 */
function generateBaselines(historicalData) {
  // historicalData: [{dayOfWeek:0-6, elec, water, gas}]
  const days = Array.from({ length: 7 }, () => ({
    elec: [],
    water: [],
    gas: [],
  }));
  historicalData.forEach((d) => {
    days[d.dayOfWeek].elec.push(d.elec);
    days[d.dayOfWeek].water.push(d.water);
    days[d.dayOfWeek].gas.push(d.gas);
  });
  return days.map((d, i) => ({
    day: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"][i],
    elec: avg(d.elec),
    water: avg(d.water),
    gas: avg(d.gas),
  }));
}

/**
 * FUNCTION 5 — Multi-Unit Conversion Manager
 * Switches between metric/imperial and currency symbols
 * PHP: api/settings.php?action=units
 */
function convertUnits(value, from, to) {
  const conversions = {
    "kWh->BTU": (v) => +(v * 3412.14).toFixed(1),
    "BTU->kWh": (v) => +(v / 3412.14).toFixed(4),
    "L->gal": (v) => +(v * 0.264172).toFixed(2),
    "gal->L": (v) => +(v * 3.78541).toFixed(2),
    "m3->ft3": (v) => +(v * 35.3147).toFixed(2),
    "ft3->m3": (v) => +(v / 35.3147).toFixed(4),
    "kg->lb": (v) => +(v * 2.20462).toFixed(2),
    "lb->kg": (v) => +(v / 2.20462).toFixed(2),
  };
  const key = `${from}->${to}`;
  return conversions[key] ? conversions[key](value) : value;
}

/**
 * FUNCTION 6 — Anomaly Detection Filter (Ghost Loads)
 * Identifies appliances drawing power while supposedly OFF
 * PHP: api/anomaly.php
 */
function detectAnomalies(appliances) {
  return appliances
    .filter((a) => {
      const isOff = !a.isOn;
      const hasStandby = a.standbyWatts > 5;
      const highDraw = a.currentWatts > a.expectedWatts * 1.3;
      return (isOff && hasStandby) || highDraw;
    })
    .map((a) => ({
      ...a,
      anomalyType: !a.isOn ? "ghost_load" : "overconsumption",
      severity: a.currentWatts > a.expectedWatts * 1.5 ? "high" : "medium",
    }));
}

/**
 * FUNCTION 7 — Solar / Renewable Offset Tracker
 * Subtracts generated solar energy from consumed
 * PHP: api/solar.php
 */
function calcSolarOffset(consumed, generated) {
  const net = Math.max(0, consumed - generated);
  const pct = consumed > 0 ? +((generated / consumed) * 100).toFixed(1) : 0;
  const exported = Math.max(0, generated - consumed);
  const savings = +(generated * getSettings().tariff.offPeak).toFixed(2);
  return { net, pct, exported, savings, generated, consumed };
}

/**
 * FUNCTION 8 — Comparative Neighbourhood Benchmarking
 * Compares user usage vs anonymous neighbourhood average
 * PHP: api/benchmark.php
 */
function neighbourhoodBenchmark(userUsage) {
  // Simulated neighbourhood average (PHP: SELECT AVG(...) FROM usage_aggregate)
  const neighbourhood = { elec: 22.5, water: 185, gas: 4.1 };
  return {
    elec: {
      user: userUsage.elec,
      avg: neighbourhood.elec,
      diff: +(
        ((userUsage.elec - neighbourhood.elec) / neighbourhood.elec) *
        100
      ).toFixed(1),
    },
    water: {
      user: userUsage.water,
      avg: neighbourhood.water,
      diff: +(
        ((userUsage.water - neighbourhood.water) / neighbourhood.water) *
        100
      ).toFixed(1),
    },
    gas: {
      user: userUsage.gas,
      avg: neighbourhood.gas,
      diff: +(
        ((userUsage.gas - neighbourhood.gas) / neighbourhood.gas) *
        100
      ).toFixed(1),
    },
  };
}

/**
 * FUNCTION 9 — Historical Data Aggregator
 * Rolls up minute data into daily/weekly/monthly summaries
 * PHP: api/history.php?period=weekly
 */
function aggregateHistory(rawData, period = "daily") {
  if (period === "daily") return rawData.map((d) => ({ ...d, label: d.date }));
  if (period === "weekly") {
    const weeks = {};
    rawData.forEach((d) => {
      const wk = getWeekNumber(new Date(d.date));
      if (!weeks[wk])
        weeks[wk] = {
          label: `Week ${wk}`,
          elec: 0,
          water: 0,
          gas: 0,
          count: 0,
        };
      weeks[wk].elec += d.elec;
      weeks[wk].water += d.water;
      weeks[wk].gas += d.gas;
      weeks[wk].count++;
    });
    return Object.values(weeks);
  }
  if (period === "monthly") {
    const months = {};
    rawData.forEach((d) => {
      const mo = d.date.substring(0, 7);
      if (!months[mo]) months[mo] = { label: mo, elec: 0, water: 0, gas: 0 };
      months[mo].elec += d.elec;
      months[mo].water += d.water;
      months[mo].gas += d.gas;
    });
    return Object.values(months);
  }
}

/**
 * FUNCTION 10 — Resource-to-Task Translator
 * Converts abstract kWh into relatable analogies
 * PHP: api/translate.php
 */
function resourceToTask(kWh) {
  const tasks = [
    { label: "loads of laundry", factor: 0.43 },
    { label: "hours of TV", factor: 0.1 },
    { label: "phone charges", factor: 0.012 },
    { label: "dishwasher cycles", factor: 1.2 },
    { label: "hours of AC", factor: 1.5 },
    { label: "LED bulb-hours", factor: 0.01 },
  ];
  return tasks.map((t) => ({
    label: t.label,
    quantity: Math.round(kWh / t.factor),
  }));
}

/**
 * FUNCTION 11 — Weather-Correlation Engine
 * Adjusts expected gas usage based on temperature
 * PHP: api/weather.php (calls OpenWeather API server-side)
 */
function weatherCorrelation(baselineGas, tempC, baselineTempC = 18) {
  const delta = baselineTempC - tempC;
  const heatFactor = delta > 0 ? 1 + delta * 0.04 : 1 + delta * 0.02;
  const expected = +(baselineGas * heatFactor).toFixed(2);
  return { expected, heatFactor: +heatFactor.toFixed(3), tempC, delta };
}

/* ═══════════════════════════════════════════════════════════
   ██████  FUNCTION GROUP B — Appliance & Health (11 Functions)
═══════════════════════════════════════════════════════════ */

/** Appliance DB stored in localStorage — PHP: appliances table */
const ApplianceDB = {
  getAll() {
    const u = Auth.getUser();
    if (!u) return defaultAppliances();
    return (
      JSON.parse(localStorage.getItem(`aot_appliances_${u.id}`) || "null") ||
      defaultAppliances()
    );
  },
  save(appliances) {
    const u = Auth.getUser();
    if (!u) return;
    localStorage.setItem(`aot_appliances_${u.id}`, JSON.stringify(appliances));
  },
  update(id, data) {
    const all = this.getAll();
    const idx = all.findIndex((a) => a.id === id);
    if (idx !== -1) {
      all[idx] = { ...all[idx], ...data };
      this.save(all);
    }
    return all;
  },
};

/**
 * FUNCTION 12 — Appliance Signature Library
 * OO repository of energy profiles per device type
 * PHP: SELECT * FROM appliance_profiles WHERE type=?
 */
const ApplianceSignatures = {
  HVAC: { watts: 3500, standby: 5, category: "Climate", icon: "❄️" },
  Refrigerator: { watts: 150, standby: 150, category: "Kitchen", icon: "🧊" },
  WashingMachine: { watts: 500, standby: 2, category: "Laundry", icon: "🫧" },
  Dishwasher: { watts: 1200, standby: 1, category: "Kitchen", icon: "🍽️" },
  EVCharger: { watts: 7200, standby: 10, category: "Transport", icon: "🔌" },
  WaterHeater: { watts: 4500, standby: 50, category: "Plumbing", icon: "🚿" },
  Oven: { watts: 2400, standby: 2, category: "Kitchen", icon: "🍳" },
  TV: { watts: 120, standby: 15, category: "Entertainment", icon: "📺" },
  PC: { watts: 300, standby: 5, category: "Electronics", icon: "💻" },
  Lighting: { watts: 60, standby: 0, category: "Lighting", icon: "💡" },
};

/**
 * FUNCTION 13 — Health Degradation Simulator
 * Gradually increases power draw to simulate wear
 * PHP: UPDATE appliances SET efficiency=? WHERE id=?
 */
function simulateDegradation(appliance) {
  const ageYears =
    (Date.now() - new Date(appliance.installedAt).getTime()) /
    (1000 * 60 * 60 * 24 * 365);
  const degradation = Math.min(0.4, ageYears * 0.03); // max 40% degradation
  const currentWatts = Math.round(appliance.nominalWatts * (1 + degradation));
  const efficiency = +((1 - degradation) * 100).toFixed(1);
  return {
    ...appliance,
    currentWatts,
    efficiency,
    ageYears: +ageYears.toFixed(1),
  };
}

/**
 * FUNCTION 14 — Maintenance Alert Trigger
 * Fires when efficiency drops below threshold
 * PHP: INSERT INTO alerts (type, appliance_id, msg) VALUES (...)
 */
function checkMaintenanceAlerts(appliances) {
  const alerts = [];
  appliances.forEach((a) => {
    if (a.efficiency < 75)
      alerts.push({
        appliance: a.name,
        type: "efficiency",
        severity: "high",
        msg: `${a.name} efficiency at ${a.efficiency}% — service needed.`,
      });
    else if (a.efficiency < 88)
      alerts.push({
        appliance: a.name,
        type: "efficiency",
        severity: "warn",
        msg: `${a.name} running at ${a.efficiency}% efficiency. Consider servicing.`,
      });
    if (a.health === "Overloaded")
      alerts.push({
        appliance: a.name,
        type: "overload",
        severity: "high",
        msg: `${a.name} is overloaded! Check circuit breaker.`,
      });
  });
  return alerts;
}

/**
 * FUNCTION 15 — Smart-Plug Association Logic
 * Links an appliance to a specific virtual monitor
 * PHP: UPDATE appliances SET monitor_id=? WHERE id=?
 */
function assignToMonitor(applianceId, monitorId) {
  const all = ApplianceDB.getAll();
  return ApplianceDB.update(applianceId, { monitorId });
}

/**
 * FUNCTION 16 — Appliance Lifecycle Tracker
 * Suggests upgrades when newer models save money
 * PHP: SELECT * FROM appliance_models WHERE type=? AND efficiency > ?
 */
function checkLifecycle(appliance) {
  const maxLife = {
    HVAC: 15,
    Refrigerator: 13,
    WashingMachine: 10,
    EVCharger: 8,
    WaterHeater: 12,
  };
  const years =
    (Date.now() - new Date(appliance.installedAt).getTime()) /
    (1000 * 60 * 60 * 24 * 365);
  const limit = maxLife[appliance.type] || 10;
  const nearEnd = years / limit > 0.8;
  const annualWaste = nearEnd
    ? Math.round(
        (((appliance.currentWatts - appliance.nominalWatts) * 8760) / 1000) *
          0.15,
      )
    : 0;
  return { years: +years.toFixed(1), limit, nearEnd, annualWaste };
}

/**
 * FUNCTION 17 — Duty Cycle Monitor
 * Tracks on/off cycles to detect short-cycling
 * PHP: SELECT COUNT(*) FROM appliance_log WHERE appliance_id=? AND created_at > NOW()-INTERVAL 1 HOUR
 */
function monitorDutyCycle(applianceId, logs) {
  const recent = logs.filter(
    (l) =>
      l.applianceId === applianceId &&
      Date.now() - new Date(l.ts).getTime() < 3600000,
  );
  const cycles = recent.filter((l) => l.action === "ON").length;
  const isShortCycling = cycles > 8; // >8 cycles/hour = problem
  return {
    cycles,
    isShortCycling,
    status: isShortCycling ? "Short-cycling detected" : "Normal",
  };
}

/**
 * FUNCTION 18 — Standby Power Management (Vampire Power)
 * Lists devices wasting the most standby power
 * PHP: SELECT * FROM appliances WHERE status='OFF' ORDER BY standby_watts DESC
 */
function getVampireDevices(appliances) {
  return appliances
    .filter((a) => !a.isOn && a.standbyWatts > 0)
    .sort((a, b) => b.standbyWatts - a.standbyWatts)
    .map((a) => ({
      name: a.name,
      standbyWatts: a.standbyWatts,
      dailyCost: +((a.standbyWatts / 1000) * 24 * 0.12).toFixed(3),
      annualCost: +((a.standbyWatts / 1000) * 24 * 365 * 0.12).toFixed(2),
    }));
}

/**
 * FUNCTION 19 — Appliance Permission Levels (RBAC)
 * Restricts control of high-draw appliances by role
 * PHP: SELECT permissions FROM roles WHERE role=?
 */
function canToggleAppliance(userRole, applianceWatts) {
  const limits = { guest: 0, tenant: 1500, owner: Infinity };
  return applianceWatts <= (limits[userRole] || 0);
}

/**
 * FUNCTION 20 — Device Firmware Update Simulator
 * Manages virtual sensor firmware versions
 * PHP: UPDATE sensors SET firmware_version=?, last_updated=NOW() WHERE id=?
 */
function simulateFirmwareUpdate(sensorId) {
  const sensors = JSON.parse(localStorage.getItem("aot_sensors") || "[]");
  const idx = sensors.findIndex((s) => s.id === sensorId);
  if (idx !== -1) {
    sensors[idx] = {
      ...sensors[idx],
      firmware: "2.4.1",
      lastUpdated: new Date().toISOString(),
      status: "updated",
    };
    localStorage.setItem("aot_sensors", JSON.stringify(sensors));
  }
  return sensors[idx];
}

/**
 * FUNCTION 21 — Appliance Interaction Log
 * Non-repudiable history of every toggle
 * PHP: INSERT INTO appliance_log (user_id, appliance_id, action, ts) VALUES (...)
 */
function logApplianceAction(applianceId, action, userId) {
  const logs = JSON.parse(localStorage.getItem("aot_app_log") || "[]");
  logs.unshift({
    id: Date.now(),
    applianceId,
    action,
    userId,
    ts: new Date().toISOString(),
  });
  localStorage.setItem("aot_app_log", JSON.stringify(logs.slice(0, 500)));
}

/**
 * FUNCTION 22 — Emergency Shutdown Protocol
 * Cuts power to appliance on critical safety hazard
 * PHP: UPDATE appliances SET status='EMERGENCY_OFF', emergency=1 WHERE id=?
 */
function emergencyShutdown(applianceId, reason) {
  const all = ApplianceDB.getAll();
  const app = all.find((a) => a.id === applianceId);
  if (app) {
    ApplianceDB.update(applianceId, {
      isOn: false,
      health: "Emergency Off",
      emergencyReason: reason,
    });
    logApplianceAction(applianceId, "EMERGENCY_SHUTDOWN", Auth.getUser()?.id);
    showToast(`⚠️ Emergency shutdown: ${app.name}`, "danger");
  }
}

/* ═══════════════════════════════════════════════════════════
   ██████  FUNCTION GROUP C — Goals & Gamification (10 Functions)
═══════════════════════════════════════════════════════════ */

/** Goals DB — PHP: goals table */
const GoalDB = {
  getAll() {
    const u = Auth.getUser();
    return (
      JSON.parse(localStorage.getItem(`aot_goals_${u?.id}`) || "null") ||
      defaultGoals()
    );
  },
  save(goals) {
    const u = Auth.getUser();
    localStorage.setItem(`aot_goals_${u?.id}`, JSON.stringify(goals));
  },
};

/**
 * FUNCTION 23 — Dynamic Budget Adjuster
 * Suggests lower budget based on previous month's savings
 * PHP: SELECT AVG(total_cost) FROM monthly_summary WHERE user_id=? ORDER BY month DESC LIMIT 3
 */
function suggestBudgetAdjustment(goals, lastMonthActual) {
  return goals.map((g) => {
    const actual = lastMonthActual[g.type] || g.budget;
    const savings = g.budget - actual;
    const suggested = savings > 0 ? +(actual * 1.05).toFixed(2) : g.budget;
    return { ...g, suggested, savings: +savings.toFixed(2) };
  });
}

/**
 * FUNCTION 24 — Eco-Challenge Engine (state machine)
 * Manages time-limited challenges
 * PHP: SELECT * FROM challenges WHERE user_id=? AND status='active'
 */
const EcoChallenge = {
  STATES: {
    IDLE: "idle",
    ACTIVE: "active",
    COMPLETED: "completed",
    FAILED: "failed",
  },
  available: [
    {
      id: "water10",
      name: "Water Saver",
      desc: "Reduce water by 10% this week",
      resource: "water",
      target: -10,
      days: 7,
      reward: 50,
    },
    {
      id: "nogas3",
      name: "Gas-Free Days",
      desc: "Use no heating gas for 3 days",
      resource: "gas",
      target: 0,
      days: 3,
      reward: 30,
    },
    {
      id: "peak5",
      name: "Peak Dodger",
      desc: "Zero electricity usage 6–9pm for 5 days",
      resource: "elec",
      target: 0,
      days: 5,
      reward: 40,
    },
  ],
  getActive() {
    const u = Auth.getUser();
    return JSON.parse(localStorage.getItem(`aot_challenges_${u?.id}`) || "[]");
  },
  start(challengeId) {
    const c = this.available.find((c) => c.id === challengeId);
    if (!c) return;
    const u = Auth.getUser();
    const active = this.getActive();
    active.push({
      ...c,
      state: this.STATES.ACTIVE,
      startedAt: new Date().toISOString(),
      progress: 0,
    });
    localStorage.setItem(`aot_challenges_${u?.id}`, JSON.stringify(active));
    return c;
  },
  updateProgress(challengeId, currentValue) {
    const u = Auth.getUser();
    const active = this.getActive();
    const idx = active.findIndex((c) => c.id === challengeId);
    if (idx === -1) return;
    const c = active[idx];
    const progress =
      c.target === 0
        ? currentValue === 0
          ? c.progress + 1
          : c.progress
        : Math.min(100, Math.abs(currentValue / c.target) * 100);
    const state = progress >= 100 ? this.STATES.COMPLETED : c.state;
    active[idx] = { ...c, progress, state };
    if (state === this.STATES.COMPLETED) RewardSystem.addCredits(c.reward);
    localStorage.setItem(`aot_challenges_${u?.id}`, JSON.stringify(active));
    return active[idx];
  },
};

/**
 * FUNCTION 25 — Reward Points Logic (Eco-Credits)
 * Calculates virtual credits from sustainable habits
 * PHP: UPDATE users SET eco_credits=eco_credits+? WHERE id=?
 */
const RewardSystem = {
  getCredits() {
    const u = Auth.getUser();
    return +(localStorage.getItem(`aot_credits_${u?.id}`) || 0);
  },
  addCredits(amount) {
    const u = Auth.getUser();
    const current = this.getCredits();
    localStorage.setItem(`aot_credits_${u?.id}`, current + amount);
    showToast(`+${amount} Eco-Credits earned! 🌿`, "ok");
    return current + amount;
  },
  calcDailyCredits(usage, baselines) {
    let credits = 10; // base daily
    if (usage.elec < baselines.elec * 0.9) credits += 15;
    if (usage.water < baselines.water * 0.9) credits += 10;
    if (usage.gas < baselines.gas * 0.9) credits += 10;
    return credits;
  },
};

/**
 * FUNCTION 26 — Scenario "What-If" Simulator
 * Shows savings if user replaces a specific appliance
 * PHP: api/scenarios.php
 */
function whatIfSimulator(oldAppliance, newEfficiencyPct) {
  const currentAnnualKWh =
    (oldAppliance.currentWatts / 1000) * oldAppliance.hoursPerDay * 365;
  const newWatts = oldAppliance.nominalWatts * (newEfficiencyPct / 100);
  const newAnnualKWh = (newWatts / 1000) * oldAppliance.hoursPerDay * 365;
  const savedKWh = currentAnnualKWh - newAnnualKWh;
  const savedCost = +(savedKWh * 0.15).toFixed(2);
  const savedCO2 = +(savedKWh * 0.233).toFixed(2);
  const paybackYears = oldAppliance.replacementCost
    ? +(oldAppliance.replacementCost / savedCost).toFixed(1)
    : null;
  return { savedKWh: +savedKWh.toFixed(1), savedCost, savedCO2, paybackYears };
}

/**
 * FUNCTION 27 — Departmental / Room Budgeting
 * Allocates resource allowances to rooms
 * PHP: SELECT * FROM room_budgets WHERE home_id=?
 */
function getRoomBudgets() {
  const u = Auth.getUser();
  return (
    JSON.parse(localStorage.getItem(`aot_rooms_${u?.id}`) || "null") || [
      { room: "Kitchen", budget: 30, spent: 24, icon: "🍳" },
      { room: "Living Room", budget: 25, spent: 18, icon: "🛋️" },
      { room: "Bedroom", budget: 15, spent: 12, icon: "🛏️" },
      { room: "Garage", budget: 20, spent: 8, icon: "🚗" },
      { room: "Bathroom", budget: 10, spent: 9, icon: "🚿" },
    ]
  );
}

/**
 * FUNCTION 28 — Peak-Shaving Advisor
 * Suggests best times to run heavy appliances
 * PHP: api/peak_advisor.php — uses tariff schedule
 */
function peakShavingAdvice(tariff) {
  const hours = Array.from({ length: 24 }, (_, h) => {
    const isPeak = h >= tariff.peakStart && h < tariff.peakEnd;
    return {
      hour: h,
      label: `${String(h).padStart(2, "0")}:00`,
      isPeak,
      rate: isPeak ? tariff.peak : tariff.offPeak,
    };
  });
  const best = hours
    .filter((h) => !h.isPeak)
    .sort((a, b) => a.rate - b.rate)
    .slice(0, 3);
  const avoid = hours.filter((h) => h.isPeak);
  return { best, avoid, cheapestHour: best[0] };
}

/**
 * FUNCTION 29 — Sustainability Milestone Tracker
 * Tracks long-term CO2 savings achievements
 * PHP: SELECT SUM(co2_saved) FROM usage_log WHERE user_id=?
 */
function checkMilestones(totalCO2Saved) {
  const milestones = [
    { kg: 10, label: "🌱 First Steps", desc: "10 kg CO₂ saved" },
    { kg: 100, label: "🌿 Green Habit", desc: "100 kg CO₂ saved" },
    {
      kg: 500,
      label: "🌳 Tree Planter",
      desc: "Equivalent to planting 5 trees",
    },
    { kg: 1000, label: "🌍 Climate Champion", desc: "1 tonne CO₂ saved!" },
    { kg: 5000, label: "⭐ Sustainability Star", desc: "5 tonnes CO₂ saved" },
  ];
  return milestones.map((m) => ({
    ...m,
    achieved: totalCO2Saved >= m.kg,
    progress: Math.min(100, (totalCO2Saved / m.kg) * 100),
  }));
}

/**
 * FUNCTION 30 — Peer Comparison Leaderboard
 * Simulated social Eco-Score comparison
 * PHP: SELECT name, eco_score FROM users WHERE home_id IN (friends) ORDER BY eco_score DESC
 */
function getLeaderboard() {
  const u = Auth.getUser();
  const simulated = [
    { name: "Sara K.", score: 920, rank: 1, trend: "up" },
    { name: "Ahmed M.", score: 880, rank: 2, trend: "up" },
    { name: "Layla R.", score: 845, rank: 3, trend: "down" },
    { name: "Omar F.", score: 810, rank: 4, trend: "same" },
  ];
  const userScore = RewardSystem.getCredits() + 750;
  const userEntry = {
    name: u ? `${u.firstName} (You)` : "You",
    score: userScore,
    isMe: true,
  };
  return [...simulated, userEntry]
    .sort((a, b) => b.score - a.score)
    .map((e, i) => ({ ...e, rank: i + 1 }));
}

/**
 * FUNCTION 31 — Budget Overrun Escalation
 * Multi-stage notification as usage approaches limit
 * PHP: Cron job: SELECT * FROM goals WHERE spent/budget > 0.8
 */
function checkBudgetEscalation(goals) {
  const alerts = [];
  goals.forEach((g) => {
    const pct = g.spent / g.budget;
    if (pct >= 1.0)
      alerts.push({
        ...g,
        level: "critical",
        msg: `${g.label} budget EXCEEDED! $${(g.spent - g.budget).toFixed(0)} over limit.`,
      });
    else if (pct >= 0.9)
      alerts.push({
        ...g,
        level: "danger",
        msg: `${g.label} at 90% — only $${(g.budget - g.spent).toFixed(0)} remaining.`,
      });
    else if (pct >= 0.8)
      alerts.push({
        ...g,
        level: "warn",
        msg: `${g.label} at 80% of budget. Monitor closely.`,
      });
  });
  return alerts;
}

/**
 * FUNCTION 32 — Vacation Mode Logic
 * Ultra-low thresholds + alert for any activity
 * PHP: UPDATE homes SET vacation_mode=1, vacation_start=NOW() WHERE id=?
 */
const VacationMode = {
  isActive() {
    const u = Auth.getUser();
    return JSON.parse(localStorage.getItem(`aot_vacation_${u?.id}`) || "false");
  },
  toggle() {
    const u = Auth.getUser();
    const active = !this.isActive();
    localStorage.setItem(`aot_vacation_${u?.id}`, JSON.stringify(active));
    if (active)
      showToast("🏖️ Vacation Mode ON — monitoring for any activity", "info");
    else showToast("🏠 Vacation Mode OFF — normal thresholds restored", "ok");
    return active;
  },
  checkActivity(usage) {
    if (!this.isActive()) return false;
    const threshold = { elec: 0.5, water: 5, gas: 0.1 };
    const triggered =
      usage.elec > threshold.elec ||
      usage.water > threshold.water ||
      usage.gas > threshold.gas;
    if (triggered)
      showToast("⚠️ Activity detected in vacation-mode home!", "danger");
    return triggered;
  },
};

/* ═══════════════════════════════════════════════════════════
   ██████  FUNCTION GROUP D — Automation & Infrastructure (10 Functions)
═══════════════════════════════════════════════════════════ */

/**
 * FUNCTION 33 — Rule-Based Logic Engine (IFTTT)
 * Users create "If-This-Then-That" automation rules
 * PHP: SELECT * FROM rules WHERE user_id=? AND active=1
 */
const RuleEngine = {
  getRules() {
    const u = Auth.getUser();
    return (
      JSON.parse(localStorage.getItem(`aot_rules_${u?.id}`) || "null") ||
      defaultRules()
    );
  },
  saveRules(rules) {
    const u = Auth.getUser();
    localStorage.setItem(`aot_rules_${u?.id}`, JSON.stringify(rules));
  },
  evaluate(rules, sensorData) {
    const triggered = [];
    rules
      .filter((r) => r.active)
      .forEach((rule) => {
        const val = sensorData[rule.ifResource];
        let condMet = false;
        if (rule.ifOperator === ">" && val > rule.ifValue) condMet = true;
        if (rule.ifOperator === "<" && val < rule.ifValue) condMet = true;
        if (rule.ifOperator === ">=" && val >= rule.ifValue) condMet = true;
        if (rule.ifOperator === "<=" && val <= rule.ifValue) condMet = true;
        if (condMet) {
          triggered.push(rule);
          this.executeAction(rule);
        }
      });
    return triggered;
  },
  executeAction(rule) {
    // PHP: POST api/automation.php — execute server-side action (e.g., push notification, toggle relay)
    const msg = `🤖 Rule fired: "${rule.name}" → ${rule.thenAction}`;
    showToast(msg, "info");
    logApplianceAction(rule.thenTarget, rule.thenAction, Auth.getUser()?.id);
  },
};

/**
 * FUNCTION 34 — Sensor Data Mock-Generator
 * Background ticker generating realistic simulated data
 * PHP: Replaced by real IoT sensor feed / MQTT / SSE stream
 */
const SensorGenerator = {
  _interval: null,
  _callbacks: [],

  start(intervalMs = 5000) {
    if (this._interval) return;
    this._interval = setInterval(() => {
      const data = this.generate();
      this._callbacks.forEach((cb) => cb(data));
      // Check rules on every tick
      const rules = RuleEngine.getRules();
      RuleEngine.evaluate(rules, data);
      // Check vacation mode
      VacationMode.checkActivity(data);
    }, intervalMs);
  },

  stop() {
    clearInterval(this._interval);
    this._interval = null;
  },

  onData(cb) {
    this._callbacks.push(cb);
  },

  generate() {
    const h = new Date().getHours();
    const isPeak = h >= 18 && h < 22;
    return {
      elec: +simulateUsage(1, isPeak ? 2.5 : 0.8, isPeak ? 5 : 2.5)[0].toFixed(
        2,
      ),
      water: +simulateUsage(1, 3, 18)[0].toFixed(1),
      gas: +simulateUsage(1, 0.05, 0.4)[0].toFixed(3),
      solar: +(h >= 7 && h <= 19 ? simulateUsage(1, 0.3, 1.8)[0] : 0).toFixed(
        2,
      ),
      temp: +simulateUsage(1, 18, 32)[0].toFixed(1),
      ts: new Date().toISOString(),
    };
  },
};

/**
 * FUNCTION 35 — Multi-User RBAC
 * Defines permissions for Owner / Tenant / Guest
 * PHP: roles table + middleware check
 */
const RBAC = {
  permissions: {
    owner: {
      canEdit: true,
      canToggle: true,
      maxWatts: Infinity,
      canManageUsers: true,
      canExport: true,
    },
    tenant: {
      canEdit: false,
      canToggle: true,
      maxWatts: 1500,
      canManageUsers: false,
      canExport: false,
    },
    guest: {
      canEdit: false,
      canToggle: false,
      maxWatts: 0,
      canManageUsers: false,
      canExport: false,
    },
  },
  can(action) {
    const user = Auth.getUser();
    if (!user) return false;
    const perms = this.permissions[user.role] || this.permissions.guest;
    return !!perms[action];
  },
  applyToUI() {
    const user = Auth.getUser();
    if (!user) return;
    const perms = this.permissions[user.role] || this.permissions.guest;
    if (!perms.canEdit) {
      document
        .querySelectorAll('[data-requires="edit"]')
        .forEach((el) => (el.style.display = "none"));
    }
    if (!perms.canToggle) {
      document
        .querySelectorAll(".appliance-toggle")
        .forEach((el) => (el.disabled = true));
    }
    if (!perms.canManageUsers) {
      document
        .querySelectorAll('[data-requires="manage-users"]')
        .forEach((el) => (el.style.display = "none"));
    }
  },
};

/**
 * FUNCTION 36 — Notification Channel Manager
 * Routes alerts to Dashboard / Email / SMS by priority
 * PHP: api/notify.php — sends email via PHPMailer, SMS via Twilio
 */
const NotificationManager = {
  channels: { dashboard: true, email: false, sms: false },
  queue: [],
  send(alert) {
    // PHP: if($priority === 'high') send_email($user->email, $alert); if($sms_enabled) send_sms($alert);
    this.queue.unshift({ ...alert, ts: new Date().toISOString(), read: false });
    const u = Auth.getUser();
    localStorage.setItem(
      `aot_notifs_${u?.id}`,
      JSON.stringify(this.queue.slice(0, 100)),
    );
    if (this.channels.dashboard)
      showToast(alert.msg, alert.severity === "high" ? "danger" : "warn");
    this.updateBadge();
  },
  getAll() {
    const u = Auth.getUser();
    this.queue = JSON.parse(
      localStorage.getItem(`aot_notifs_${u?.id}`) || "[]",
    );
    return this.queue;
  },
  markRead(id) {
    this.queue = this.queue.map((n) =>
      n.id === id ? { ...n, read: true } : n,
    );
  },
  updateBadge() {
    const badge = document.getElementById("notif-badge");
    const unread = this.getAll().filter((n) => !n.read).length;
    if (badge) {
      badge.textContent = unread;
      badge.style.display = unread > 0 ? "" : "none";
    }
  },
};

/**
 * FUNCTION 37 — System Self-Diagnostic
 * Monitors virtual connection status of all sensors
 * PHP: SELECT * FROM sensors WHERE last_ping < NOW()-INTERVAL 5 MINUTE
 */
function runSelfDiagnostic() {
  const sensors = [
    { id: 1, name: "Electricity Meter", status: "online", lastPing: "2s ago" },
    { id: 2, name: "Water Flow Sensor", status: "online", lastPing: "3s ago" },
    { id: 3, name: "Gas Meter", status: "online", lastPing: "5s ago" },
    { id: 4, name: "Solar Inverter", status: "online", lastPing: "4s ago" },
    { id: 5, name: "HVAC Thermostat", status: "offline", lastPing: "2m ago" },
  ];
  const health = sensors.every((s) => s.status === "online")
    ? "All systems nominal"
    : "One or more sensors offline";
  return { sensors, health, timestamp: new Date().toISOString() };
}

/**
 * FUNCTION 38 — Data Export / Report Builder
 * Generates CSV summary for home audits
 * PHP: api/export.php — generates PDF via mPDF or TCPDF
 */
function exportToCSV(data, filename = "aot-report") {
  if (!data || data.length === 0) {
    showToast("No data to export", "warn");
    return;
  }
  const headers = Object.keys(data[0]).join(",");
  const rows = data.map((r) =>
    Object.values(r)
      .map((v) => `"${v}"`)
      .join(","),
  );
  const csv = [headers, ...rows].join("\n");
  const blob = new Blob([csv], { type: "text/csv" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `${filename}-${new Date().toISOString().slice(0, 10)}.csv`;
  a.click();
  URL.revokeObjectURL(url);
  showToast("✅ Report exported as CSV", "ok");
  // PHP: header('Content-Type: application/pdf'); echo $mpdf->Output('report.pdf', 'D');
}

/**
 * FUNCTION 39 — Time-Sync Synchronization
 * Ensures charts align with user's local simulated time
 * PHP: date_default_timezone_set($user->timezone);
 */
function getTimeSync() {
  const now = new Date();
  const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
  const local = now.toLocaleString("en-GB", { timeZone: tz });
  return { now, tz, local, offset: -now.getTimezoneOffset() / 60 };
}

/**
 * FUNCTION 40 — Encryption Wrapper (frontend stub)
 * PHP handles real encryption — this is a UI indicator only
 * PHP: openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv)
 */
function sensitiveFieldMask(value, reveal = false) {
  if (reveal) return value;
  const visible = value.length > 4 ? value.slice(-4) : "****";
  return "•".repeat(Math.max(4, value.length - 4)) + visible;
  // PHP: All sensitive fields stored AES-256 encrypted in DB
}

/**
 * FUNCTION 41 — System Audit Trail
 * Logs every setting change / rule modification
 * PHP: INSERT INTO audit_log (user_id, action, old_val, new_val, ts) VALUES (...)
 */
function logAuditEvent(action, oldVal, newVal) {
  const u = Auth.getUser();
  const logs = JSON.parse(localStorage.getItem("aot_audit") || "[]");
  logs.unshift({
    id: Date.now(),
    userId: u?.id,
    user: u?.name,
    action,
    oldVal: JSON.stringify(oldVal),
    newVal: JSON.stringify(newVal),
    ts: new Date().toISOString(),
  });
  localStorage.setItem("aot_audit", JSON.stringify(logs.slice(0, 1000)));
}

/**
 * FUNCTION 42 — Database Purge & Archive Logic
 * Moves raw data to long-term storage
 * PHP: INSERT INTO usage_archive SELECT * FROM usage_raw WHERE created_at < NOW()-INTERVAL 30 DAY
 *      DELETE FROM usage_raw WHERE created_at < NOW()-INTERVAL 30 DAY
 */
function purgeOldData(retentionDays = 30) {
  const keys = Object.keys(localStorage).filter((k) => k.startsWith("aot_"));
  const cutoff = Date.now() - retentionDays * 24 * 60 * 60 * 1000;
  let purged = 0;
  // In production PHP handles this in a scheduled cron job
  // Frontend: clear simulation-specific timestamped data
  const rawData = JSON.parse(localStorage.getItem("aot_raw_usage") || "[]");
  const archived = rawData.filter((d) => new Date(d.ts).getTime() < cutoff);
  const kept = rawData.filter((d) => new Date(d.ts).getTime() >= cutoff);
  localStorage.setItem("aot_raw_usage", JSON.stringify(kept));
  localStorage.setItem(
    "aot_usage_archive",
    JSON.stringify([
      ...JSON.parse(localStorage.getItem("aot_usage_archive") || "[]"),
      ...archived,
    ]),
  );
  purged = archived.length;
  showToast(`🗄️ Archived ${purged} old records`, "info");
  logAuditEvent("DATA_PURGE", { retentionDays }, { purged });
  return { purged, kept: kept.length };
}

/* ═══════════════════════════════════════════════════════════
   CHART.JS HELPERS
═══════════════════════════════════════════════════════════ */
function applyChartDefaults() {
  if (typeof Chart === "undefined") return;
  Chart.defaults.color = "#4a6280";
  Chart.defaults.font.family = "'DM Sans', sans-serif";
  Chart.defaults.font.size = 11;
  Chart.defaults.borderColor = "#1e2a38";
  Chart.defaults.responsive = true;
  Chart.defaults.maintainAspectRatio = false;
  Chart.defaults.plugins.legend.labels.color = "#8fa3bb";
  Chart.defaults.plugins.legend.labels.boxWidth = 10;
  Chart.defaults.plugins.legend.labels.padding = 16;
  Chart.defaults.plugins.tooltip.backgroundColor = "#141a22";
  Chart.defaults.plugins.tooltip.borderColor = "#1e2a38";
  Chart.defaults.plugins.tooltip.borderWidth = 1;
  Chart.defaults.plugins.tooltip.titleColor = "#e2eaf4";
  Chart.defaults.plugins.tooltip.bodyColor = "#8fa3bb";
  Chart.defaults.plugins.tooltip.padding = 10;
  Chart.defaults.plugins.tooltip.cornerRadius = 8;
}

function makeLineChart(id, config) {
  const el = document.getElementById(id);
  if (!el || typeof Chart === "undefined") return null;
  if (el._ch) el._ch.destroy();
  const ds = config.datasets.map((d) => ({
    label: d.label,
    data: d.data,
    borderColor: d.color,
    backgroundColor: hexAlpha(d.color, 0.1),
    borderWidth: 2,
    pointRadius: 3,
    pointHoverRadius: 6,
    pointBackgroundColor: d.color,
    tension: 0.35,
    fill: true,
  }));
  const chart = new Chart(el, {
    type: "line",
    data: { labels: config.labels, datasets: ds },
    options: {
      scales: {
        x: { grid: { color: "#1e2a38" }, ticks: { color: "#4a6280" } },
        y: {
          grid: { color: "#1e2a38" },
          ticks: { color: "#4a6280" },
          beginAtZero: true,
        },
      },
    },
  });
  el._ch = chart;
  return chart;
}

function makeBarChart(id, config) {
  const el = document.getElementById(id);
  if (!el || typeof Chart === "undefined") return null;
  if (el._ch) el._ch.destroy();
  const ds = config.datasets.map((d) => ({
    label: d.label,
    data: d.data,
    backgroundColor: hexAlpha(d.color, 0.7),
    borderColor: d.color,
    borderWidth: 1,
    borderRadius: 4,
  }));
  const chart = new Chart(el, {
    type: "bar",
    data: { labels: config.labels, datasets: ds },
    options: {
      plugins: {
        tooltip: {
          callbacks: {
            label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.y}`,
          },
        },
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: "#4a6280" } },
        y: {
          grid: { color: "#1e2a38" },
          ticks: { color: "#4a6280" },
          beginAtZero: true,
        },
      },
    },
  });
  el._ch = chart;
  return chart;
}

function makeDoughnutChart(id, config) {
  const el = document.getElementById(id);
  if (!el || typeof Chart === "undefined") return null;
  if (el._ch) el._ch.destroy();
  const chart = new Chart(el, {
    type: "doughnut",
    data: {
      labels: config.labels,
      datasets: [
        {
          data: config.data,
          backgroundColor: config.colors.map((c) => hexAlpha(c, 0.8)),
          borderColor: config.colors,
          borderWidth: 2,
          hoverOffset: 8,
        },
      ],
    },
    options: { cutout: "68%", plugins: { legend: { position: "bottom" } } },
  });
  el._ch = chart;
  return chart;
}

/* ═══════════════════════════════════════════════════════════
   UI UTILITIES
═══════════════════════════════════════════════════════════ */
function showToast(msg, type = "ok", duration = 3500) {
  let container = document.getElementById("toast-container");
  if (!container) {
    container = document.createElement("div");
    container.id = "toast-container";
    document.body.appendChild(container);
  }
  const toast = document.createElement("div");
  toast.className = `toast toast--${type}`;
  toast.textContent = msg;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transform = "translateX(20px)";
    toast.style.transition = ".3s ease";
    setTimeout(() => toast.remove(), 320);
  }, duration);
}

function openModal(html) {
  const overlay = document.createElement("div");
  overlay.className = "modal-overlay";
  overlay.innerHTML = `<div class="modal">${html}</div>`;
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) overlay.remove();
  });
  document.body.appendChild(overlay);
  return overlay;
}

function closeModal() {
  document.querySelector(".modal-overlay")?.remove();
}

function hexAlpha(hex, alpha) {
  let h = hex.replace("#", "");
  if (h.length === 3)
    h = h
      .split("")
      .map((c) => c + c)
      .join("");
  const r = parseInt(h.slice(0, 2), 16),
    g = parseInt(h.slice(2, 4), 16),
    b = parseInt(h.slice(4, 6), 16);
  return `rgba(${r},${g},${b},${alpha})`;
}

function simulateUsage(n, min, max) {
  return Array.from({ length: n }, () =>
    parseFloat((Math.random() * (max - min) + min).toFixed(2)),
  );
}

function lastNDayLabels(n) {
  return Array.from({ length: n }, (_, i) => {
    const d = new Date();
    d.setDate(d.getDate() - (n - 1 - i));
    return d.toLocaleDateString("en-GB", { weekday: "short", day: "numeric" });
  });
}

function last12MonthLabels() {
  return Array.from({ length: 12 }, (_, i) => {
    const d = new Date();
    d.setMonth(d.getMonth() - (11 - i));
    return d.toLocaleDateString("en-GB", { month: "short", year: "2-digit" });
  });
}

function avg(arr) {
  return arr.length
    ? +(arr.reduce((a, b) => a + b, 0) / arr.length).toFixed(2)
    : 0;
}

function getWeekNumber(d) {
  d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
  d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay() || 7));
  const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
  return Math.ceil(((d - yearStart) / 86400000 + 1) / 7);
}

function startLiveClock() {
  const tick = () => {
    const now = new Date();
    document
      .querySelectorAll("#live-clock")
      .forEach((el) => (el.textContent = now.toLocaleTimeString("en-GB")));
    document.querySelectorAll("#today-date").forEach(
      (el) =>
        (el.textContent = now.toLocaleDateString("en-GB", {
          weekday: "long",
          year: "numeric",
          month: "long",
          day: "numeric",
        })),
    );
  };
  tick();
  setInterval(tick, 1000);
}

function markActiveNav() {
  const page = window.location.pathname.split("/").pop() || "index.php";
  document.querySelectorAll(".nav__item").forEach((el) => {
    const href = el.getAttribute("href") || "";
    el.classList.toggle(
      "active",
      href === page || (page === "" && href === "index.php"),
    );
  });
}

function getSettings() {
  const u = Auth.getUser();
  return (
    JSON.parse(localStorage.getItem(`aot_settings_${u?.id}`) || "null") || {
      tariff: { peak: 0.28, offPeak: 0.12, peakStart: 18, peakEnd: 22 },
      currency: "$",
      units: "metric",
    }
  );
}

/**
 * Sync settings + tariffs from api/settings.php into localStorage.
 * Called on page load so getSettings() always reflects the DB values.
 * PHP: GET api/settings.php?action=get_profile  +  GET api/settings.php?action=get_tariffs
 */
async function syncSettingsFromAPI() {
  const u =
    Auth.getUser() || (typeof AOT_USER !== "undefined" ? AOT_USER : null);
  if (!u) return;

  try {
    // 1. Pull profile (currency + units)
    const profileFD = new FormData();
    profileFD.append("action", "get_profile");
    const profileRes = await fetch("/AOT/api/settings.php", {
      method: "POST",
      body: profileFD,
    });
    const profileData = await profileRes.json();

    // 2. Pull tariffs
    const tariffFD = new FormData();
    tariffFD.append("action", "get_tariffs");
    const tariffRes = await fetch("/AOT/api/settings.php", {
      method: "POST",
      body: tariffFD,
    });
    const tariffData = await tariffRes.json();

    if (!profileData.ok || !tariffData.ok) return;

    const profile = profileData.user || {};
    const tariffs = tariffData.tariffs || {};
    const elec = tariffs.electricity || {};

    // Map currency symbol
    const currencyMap = { USD: "$", EUR: "€", GBP: "£", EGP: "E£" };
    const currencySymbol =
      currencyMap[profile.currency] || profile.currency || "$";

    const settings = {
      currency: currencySymbol,
      units: profile.units || "metric",
      tariff: {
        peak: parseFloat(elec.peak_rate ?? elec.rate ?? 0.28),
        offPeak: parseFloat(elec.offpeak_rate ?? 0.12),
        peakStart: parseInt(elec.peak_start ?? "18", 10),
        peakEnd: parseInt(elec.peak_end ?? "22", 10),
      },
      rates: {
        water: parseFloat(tariffs.water?.rate ?? 0.005),
        gas: parseFloat(tariffs.gas?.rate ?? 0.45),
      },
    };

    localStorage.setItem(`aot_settings_${u.id}`, JSON.stringify(settings));
  } catch (err) {
    console.warn(
      "syncSettingsFromAPI: could not reach API, using cached settings.",
      err,
    );
  }
}

/* ── Default data ───────────────────────────────────────── */
function defaultAppliances() {
  return [
    {
      id: 1,
      name: "HVAC Unit",
      type: "HVAC",
      isOn: true,
      currentWatts: 3600,
      nominalWatts: 3500,
      standbyWatts: 5,
      efficiency: 96,
      health: "Good",
      hoursPerDay: 8,
      installedAt: "2020-01-01",
      monitorId: 1,
    },
    {
      id: 2,
      name: "Refrigerator",
      type: "Refrigerator",
      isOn: true,
      currentWatts: 165,
      nominalWatts: 150,
      standbyWatts: 150,
      efficiency: 88,
      health: "Check Filter",
      hoursPerDay: 24,
      installedAt: "2018-06-15",
      monitorId: 1,
    },
    {
      id: 3,
      name: "EV Charger",
      type: "EVCharger",
      isOn: false,
      currentWatts: 0,
      nominalWatts: 7200,
      standbyWatts: 10,
      efficiency: 99,
      health: "Good",
      hoursPerDay: 4,
      installedAt: "2022-03-20",
      monitorId: 2,
    },
    {
      id: 4,
      name: "Washing Machine",
      type: "WashingMachine",
      isOn: true,
      currentWatts: 620,
      nominalWatts: 500,
      standbyWatts: 2,
      efficiency: 72,
      health: "Overloaded",
      hoursPerDay: 2,
      installedAt: "2016-08-10",
      monitorId: 2,
    },
    {
      id: 5,
      name: "Water Heater",
      type: "WaterHeater",
      isOn: true,
      currentWatts: 4500,
      nominalWatts: 4500,
      standbyWatts: 50,
      efficiency: 95,
      health: "Good",
      hoursPerDay: 3,
      installedAt: "2021-11-05",
      monitorId: 1,
    },
    {
      id: 6,
      name: "Dishwasher",
      type: "Dishwasher",
      isOn: false,
      currentWatts: 0,
      nominalWatts: 1200,
      standbyWatts: 1,
      efficiency: 98,
      health: "Good",
      hoursPerDay: 1,
      installedAt: "2023-01-01",
      monitorId: 3,
    },
  ];
}

function defaultGoals() {
  return [
    {
      id: 1,
      type: "elec",
      label: "Electricity",
      budget: 50,
      spent: 38,
      icon: "⚡",
      color: "var(--elec)",
    },
    {
      id: 2,
      type: "water",
      label: "Water",
      budget: 30,
      spent: 29,
      icon: "💧",
      color: "var(--water)",
    },
    {
      id: 3,
      type: "gas",
      label: "Gas",
      budget: 40,
      spent: 17,
      icon: "🔥",
      color: "var(--gas)",
    },
  ];
}

function defaultRules() {
  return [
    {
      id: 1,
      name: "High Water Alert",
      active: true,
      ifResource: "water",
      ifOperator: ">",
      ifValue: 20,
      thenAction: "NOTIFY",
      thenTarget: null,
      createdAt: "2024-01-10",
    },
    {
      id: 2,
      name: "Peak Hour Reminder",
      active: true,
      ifResource: "elec",
      ifOperator: ">",
      ifValue: 4,
      thenAction: "NOTIFY",
      thenTarget: null,
      createdAt: "2024-01-12",
    },
    {
      id: 3,
      name: "Gas Safety Shutoff",
      active: false,
      ifResource: "gas",
      ifOperator: ">=",
      ifValue: 1.5,
      thenAction: "SHUTOFF",
      thenTarget: 5,
      createdAt: "2024-02-01",
    },
  ];
}
/* ── INIT ──────────────────────────────────────────────── */
document.addEventListener("DOMContentLoaded", () => {
  // Apply Chart.js defaults FIRST
  applyChartDefaults();

  // Sync settings from DB so tariff/currency are always up-to-date
  syncSettingsFromAPI();

  startLiveClock();
  markActiveNav();
  populateSidebar();
  RBAC.applyToUI();
  NotificationManager.updateBadge();

  // Logout buttons — redirect to PHP backend
  ["logout-btn", "logout-btn2"].forEach((id) => {
    const btn = document.getElementById(id);
    if (btn) {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        if (confirm("Sign out of AOT Homes?")) {
          window.location.href = "/AOT/api/logout.php";
        }
      });
    }
  });
});
