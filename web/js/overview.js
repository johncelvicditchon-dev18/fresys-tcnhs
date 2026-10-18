// ---------------------------------------------------------------------------
// Dashboard overview charts (faithful port of the original overview.js)
// ---------------------------------------------------------------------------
const centerTextPlugin = {
  id: "centerText",
  afterDraw(chart) {
    const cfg = chart.options.plugins.centerText;
    if (!cfg) return;
    const { ctx } = chart;
    const d = chart.data.datasets[0]?.data;
    if (!d || d.length < 2) return;
    const a = Number(d[0]) || 0;
    const b = Number(d[1]) || 0;
    const total = a + b;
    const maxVal = Math.max(a, b);
    const cx = chart.width / 2;
    const cy = chart.height / 2;
    ctx.save();
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    if (total === 0) {
      ctx.font = "700 1.1rem Inter, system-ui, sans-serif";
      ctx.fillStyle = "#CBD5E1";
      ctx.fillText("No data", cx, cy);
      ctx.restore();
      return;
    }
    const pct = cfg.useMax ? Math.round((maxVal / total) * 100) : Math.round((a / total) * 100);
    ctx.font = "800 1.35rem Inter, system-ui, sans-serif";
    ctx.fillStyle = cfg.color || "#0F172A";
    ctx.fillText(pct + "%", cx, cy - 8);
    ctx.font = "600 0.52rem Inter, system-ui, sans-serif";
    ctx.fillStyle = "#94A3B8";
    ctx.fillText(cfg.label || "", cx, cy + 10);
    if (cfg.showTotal) {
      ctx.font = "700 0.58rem Inter, system-ui, sans-serif";
      ctx.fillStyle = "#64748B";
      ctx.fillText("Total: " + total, cx, cy + 24);
    }
    ctx.restore();
  }
};

const CLR = {
  indigo: "#4F46E5", indigoL: "#A5B4FC",
  green: "#10B981", greenL: "#6EE7B7",
  red: "#EF4444", redL: "#FCA5A5",
  amber: "#F59E0B", amberL: "#FCD34D",
  sky: "#0EA5E9", skyL: "#7DD3FC",
  rose: "#F43F5E", roseL: "#FDA4AF",
  purple: "#8B5CF6", purpleL: "#C4B5FD",
  text: "#0F172A", muted: "#94A3B8"
};

const TOOLTIP = {
  backgroundColor: "rgba(15,23,42,0.92)",
  titleFont: { family: "'Inter',system-ui", size: 12, weight: "600" },
  bodyFont: { family: "'Inter',system-ui", size: 12 },
  padding: { top: 10, bottom: 10, left: 14, right: 14 },
  cornerRadius: 10, boxPadding: 4, usePointStyle: true
};

let charts = {};

function safeInt(v) { const n = parseInt(v, 10); return isNaN(n) ? 0 : n; }

function makeDoughnut(id, labels, colors, centerLabel, showTotal, useMax) {
  const el = document.getElementById(id);
  if (!el || !window.Chart) return null;
  return new Chart(el, {
    type: "doughnut",
    data: {
      labels,
      datasets: [{
        data: [0, 0], backgroundColor: colors, borderWidth: 3,
        borderColor: "#FFFFFF", hoverOffset: 8, spacing: 2
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: "70%", radius: "92%",
      animation: { animateRotate: true, duration: 700, easing: "easeOutQuart" },
      layout: { padding: 2 },
      plugins: {
        legend: { display: false },
        centerText: { label: centerLabel, color: CLR.text, showTotal: !!showTotal, useMax: !!useMax },
        tooltip: {
          ...TOOLTIP,
          callbacks: {
            label(ctx) {
              const d = ctx.dataset.data;
              const total = (Number(d[0]) || 0) + (Number(d[1]) || 0);
              const val = Number(ctx.parsed) || 0;
              const pct = total > 0 ? ((val / total) * 100).toFixed(1) : "0.0";
              return ` ${ctx.label}: ${val} (${pct}%)`;
            }
          }
        }
      }
    }
  });
}

function renderLegend(id, labels, colors, values) {
  const el = document.getElementById(id);
  if (!el) return;
  const total = (Number(values[0]) || 0) + (Number(values[1]) || 0);
  el.innerHTML = labels.map((label, i) => {
    const v = Number(values[i]) || 0;
    const pct = total > 0 ? ((v / total) * 100).toFixed(1) : "0.0";
    return `<div class="chart-legend-item">
      <span class="chart-legend-dot" style="background:${colors[i]}"></span>
      <span>${label} <strong>${v}</strong> (${pct}%)</span>
    </div>`;
  }).join("");
}

function setChart(chart, data, legendId, labels, colors) {
  if (!chart) return;
  chart.data.datasets[0].data = data;
  chart.update();
  renderLegend(legendId, labels, colors, data);
}

function makeGradeTurnout(labels, totalData, castedData) {
  const el = document.getElementById("chartGradeTurnout");
  if (!el || !window.Chart) return null;
  return new Chart(el, {
    type: "bar",
    data: {
      labels,
      datasets: [
        { label: "Registered", data: totalData, backgroundColor: "#E2E8F0", borderColor: "#CBD5E1", borderWidth: 1, borderRadius: 6, barPercentage: 0.8, categoryPercentage: 0.8 },
        { label: "Voted", data: castedData, backgroundColor: "#4F46E5", borderColor: "#4338CA", borderWidth: 1, borderRadius: 6, barPercentage: 0.8, categoryPercentage: 0.8 }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      animation: { duration: 700, easing: "easeOutQuart" },
      layout: { padding: { top: 10, bottom: 0, left: 10, right: 10 } },
      scales: {
        x: { grid: { display: false }, ticks: { font: { family: "'Inter',system-ui", size: 12, weight: "600" }, color: "#64748B", maxRotation: 0 } },
        y: { beginAtZero: true, grid: { color: "#F1F5F9" }, ticks: { font: { family: "'Inter',system-ui", size: 11 }, color: "#94A3B8", stepSize: 1, precision: 0 } }
      },
      plugins: {
        legend: { display: true, position: "top", align: "end", labels: { font: { family: "'Inter',system-ui", size: 11, weight: "600" }, color: "#64748B", usePointStyle: true, pointStyle: "rectRounded", padding: 16 } },
        tooltip: {
          ...TOOLTIP,
          callbacks: {
            label(ctx) {
              const val = ctx.parsed.y || 0;
              const total = ctx.chart.data.datasets[0].data[ctx.dataIndex] || 0;
              const pct = total > 0 ? ((val / total) * 100).toFixed(1) : "0.0";
              return ` ${ctx.dataset.label}: ${val} (${pct}%)`;
            }
          }
        }
      }
    }
  });
}

export function initOverviewCharts() {
  if (!window.Chart) return;
  if (Chart.registry && !Chart.registry.plugins.get("centerText")) Chart.register(centerTextPlugin);

  charts.students = makeDoughnut("chartStudents", ["SHS", "JHS"], [CLR.indigo, CLR.indigoL], "STUDENTS", true, true);
  charts.voteStatus = makeDoughnut("chartVoteStatus", ["Casted", "Not Casted"], [CLR.green, CLR.red], "VOTES");
  charts.gender = makeDoughnut("chartGender", ["Male", "Female"], [CLR.sky, CLR.rose], "GENDER");
  charts.genderVote = makeDoughnut("chartGenderVote", ["Male Casted", "Female Casted"], [CLR.sky, CLR.rose], "BY GENDER");
  charts.emptyBallot = makeDoughnut("chartEmptyBallot", ["Submitted without Choices", "Submitted with Choices"], [CLR.amber, CLR.amberL], "EMPTY BALLOT");
}

// data: { shs, jhs, casted, available, male, female, maleCasted, femaleCasted, empty, withSel, gradeTurnout:[{grade,total,casted}] }
export function refreshOverviewCharts(data) {
  if (!window.Chart) return;
  if (!charts.students) initOverviewCharts();

  const shs = safeInt(data.shs), jhs = safeInt(data.jhs);
  const casted = safeInt(data.casted), avail = safeInt(data.available);
  const male = safeInt(data.male), female = safeInt(data.female);
  const maleC = safeInt(data.maleCasted), femaleC = safeInt(data.femaleCasted);
  const empty = safeInt(data.empty), withSel = safeInt(data.withSel);

  setChart(charts.students, [shs, jhs], "legendStudents", ["SHS", "JHS"], [CLR.indigo, CLR.indigoL]);
  setChart(charts.voteStatus, [casted, avail], "legendVoteStatus", ["Casted", "Not Casted"], [CLR.green, CLR.red]);
  setChart(charts.gender, [male, female], "legendGender", ["Male", "Female"], [CLR.sky, CLR.rose]);
  setChart(charts.genderVote, [maleC, femaleC], "legendGenderVote", ["Male Casted", "Female Casted"], [CLR.sky, CLR.rose]);
  setChart(charts.emptyBallot, [empty, Math.max(0, withSel)], "legendEmptyBallot", ["Submitted without Choices", "Submitted with Choices"], [CLR.amber, CLR.amberL]);

  const gt = data.gradeTurnout || [];
  const gLabels = gt.map((g) => g.grade);
  const gTotal = gt.map((g) => g.total);
  const gCasted = gt.map((g) => g.casted);
  if (charts.gradeTurnout) {
    charts.gradeTurnout.data.labels = gLabels;
    charts.gradeTurnout.data.datasets[0].data = gTotal;
    charts.gradeTurnout.data.datasets[1].data = gCasted;
    charts.gradeTurnout.update();
  } else {
    charts.gradeTurnout = makeGradeTurnout(gLabels, gTotal, gCasted);
  }
}
