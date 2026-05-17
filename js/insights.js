const width = 980;
const height = 520;
const margin = { top: 34, right: 34, bottom: 72, left: 92 };
const chartData = { cumulative: [], services: [] };
let currentChartType = "cumulative";

const money = new Intl.NumberFormat("en-CA", {
  style: "currency",
  currency: "CAD",
});

function formatMoney(value) {
  return money.format(Number(value || 0));
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function showEmptyState(message) {
  d3.select("#chart-container")
    .html("")
    .append("p")
    .attr("class", "empty-state")
    .text(message);
}

async function loadAllData() {
  try {
    const [profitResponse, serviceResponse] = await Promise.all([
      fetch("../php/getProfit.php"),
      fetch("../php/getServiceTypeProfit.php"),
    ]);

    const [profitData, serviceData] = await Promise.all([
      profitResponse.json(),
      serviceResponse.json(),
    ]);

    if (!profitResponse.ok || !serviceResponse.ok) {
      throw new Error("Unable to load chart data.");
    }

    chartData.cumulative = profitData.map((row) => ({
      invoiceId: Number(row.InvoiceID),
      date: new Date(`${row.Date}T00:00:00`),
      revenue: Number(row.Revenue),
      cost: Number(row.Cost),
      profit: Number(row.Profit),
    }));

    chartData.services = serviceData.map((row) => ({
      serviceType: row.ServiceType,
      orderCount: Number(row.OrderCount),
      revenue: Number(row.TotalRevenue),
      cost: Number(row.TotalCost),
      profit: Number(row.TotalProfit),
    }));

    createChart(currentChartType);
  } catch (error) {
    showEmptyState("Chart data is unavailable. Check the database connection and seed data.");
  }
}

function chartShell(title) {
  const container = d3.select("#chart-container").html("");
  const svg = container
    .append("svg")
    .attr("viewBox", `0 0 ${width} ${height}`)
    .attr("role", "img")
    .attr("aria-label", title);

  svg
    .append("text")
    .attr("x", margin.left)
    .attr("y", 24)
    .attr("fill", "#17211d")
    .attr("font-size", 18)
    .attr("font-weight", 800)
    .text(title);

  return svg;
}

function tooltip() {
  return d3
    .select("body")
    .selectAll(".chart-tooltip")
    .data([null])
    .join("div")
    .attr("class", "chart-tooltip")
    .style("opacity", 0);
}

function createChart(type) {
  currentChartType = type;

  document.querySelectorAll(".chart-controls button").forEach((button) => {
    const isActive =
      (type === "cumulative" && button.id === "show-cumulative") ||
      (type === "services" && button.id === "show-service-profits");
    button.classList.toggle("active", isActive);
  });

  if (type === "cumulative") {
    createCumulativeChart();
    return;
  }

  createServiceChart();
}

function createCumulativeChart() {
  if (!chartData.cumulative.length) {
    showEmptyState("No profit trend data yet.");
    return;
  }

  const data = chartData.cumulative;
  const svg = chartShell("Cumulative Profit by Order Date");
  const tip = tooltip();

  const x = d3
    .scaleTime()
    .domain(d3.extent(data, (d) => d.date))
    .range([margin.left, width - margin.right]);

  const series = [
    { key: "revenue", label: "Revenue", color: "#1f7a4d" },
    { key: "cost", label: "Cost", color: "#b66b16" },
    { key: "profit", label: "Profit", color: "#245c8f" },
  ];

  const y = d3
    .scaleLinear()
    .domain([0, d3.max(data, (d) => Math.max(d.revenue, d.cost, d.profit)) * 1.15 || 1])
    .nice()
    .range([height - margin.bottom, margin.top]);

  svg
    .append("g")
    .attr("transform", `translate(0,${height - margin.bottom})`)
    .call(d3.axisBottom(x).ticks(6).tickSizeOuter(0));

  svg
    .append("g")
    .attr("transform", `translate(${margin.left},0)`)
    .call(d3.axisLeft(y).tickFormat(formatMoney).ticks(6));

  series.forEach((item) => {
    const line = d3
      .line()
      .x((d) => x(d.date))
      .y((d) => y(d[item.key]));

    svg
      .append("path")
      .datum(data)
      .attr("fill", "none")
      .attr("stroke", item.color)
      .attr("stroke-width", 3)
      .attr("d", line);
  });

  const legend = svg
    .append("g")
    .attr("transform", `translate(${margin.left},${height - 30})`);

  series.forEach((item, index) => {
    const group = legend.append("g").attr("transform", `translate(${index * 150},0)`);
    group.append("rect").attr("width", 14).attr("height", 14).attr("rx", 3).attr("fill", item.color);
    group.append("text").attr("x", 22).attr("y", 12).attr("fill", "#64706a").text(item.label);
  });

  svg
    .selectAll("circle")
    .data(data)
    .join("circle")
    .attr("cx", (d) => x(d.date))
    .attr("cy", (d) => y(d.profit))
    .attr("r", 5)
    .attr("fill", "#245c8f")
    .on("mousemove", (event, d) => {
      tip
        .style("opacity", 1)
        .style("left", `${event.pageX + 12}px`)
        .style("top", `${event.pageY - 34}px`)
        .html(
          `<strong>Invoice #${d.invoiceId}</strong><br>Revenue: ${formatMoney(d.revenue)}<br>Cost: ${formatMoney(d.cost)}<br>Profit: ${formatMoney(d.profit)}`
        );
    })
    .on("mouseleave", () => tip.style("opacity", 0));
}

function createServiceChart() {
  if (!chartData.services.length) {
    showEmptyState("No service mix data yet.");
    return;
  }

  const data = chartData.services;
  const svg = chartShell("Profit by Service Type");
  const tip = tooltip();

  const x = d3
    .scaleBand()
    .domain(data.map((d) => d.serviceType))
    .range([margin.left, width - margin.right])
    .padding(0.24);

  const y = d3
    .scaleLinear()
    .domain([0, d3.max(data, (d) => d.profit) * 1.15 || 1])
    .nice()
    .range([height - margin.bottom, margin.top]);

  svg
    .append("g")
    .attr("transform", `translate(0,${height - margin.bottom})`)
    .call(d3.axisBottom(x).tickSizeOuter(0))
    .selectAll("text")
    .attr("transform", "rotate(-24)")
    .attr("text-anchor", "end");

  svg
    .append("g")
    .attr("transform", `translate(${margin.left},0)`)
    .call(d3.axisLeft(y).tickFormat(formatMoney).ticks(6));

  svg
    .selectAll("rect")
    .data(data)
    .join("rect")
    .attr("x", (d) => x(d.serviceType))
    .attr("y", (d) => y(d.profit))
    .attr("width", x.bandwidth())
    .attr("height", (d) => y(0) - y(d.profit))
    .attr("rx", 4)
    .attr("fill", "#1f7a4d")
    .on("mousemove", (event, d) => {
      tip
        .style("opacity", 1)
        .style("left", `${event.pageX + 12}px`)
        .style("top", `${event.pageY - 42}px`)
        .html(
          `<strong>${escapeHtml(d.serviceType)}</strong><br>${formatMoney(d.profit)} profit<br>${d.orderCount} orders`
        );
    })
    .on("mouseleave", () => tip.style("opacity", 0));
}

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("show-cumulative").addEventListener("click", () => createChart("cumulative"));
  document.getElementById("show-service-profits").addEventListener("click", () => createChart("services"));
  loadAllData();
});
