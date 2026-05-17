const patterns = {
  clientName: /^[A-Za-z][A-Za-z0-9 .,'&-]{1,79}$/,
  jobType: /^[A-Za-z][A-Za-z0-9 .,&/-]{2,79}$/,
  money: /^\d{1,7}(\.\d{1,2})?$/,
  invoiceID: /^[1-9]\d{0,9}$/,
};

const fieldMessages = {
  clientName: "Use 2-80 letters, numbers, spaces, or common punctuation.",
  jobType: "Use 3-80 letters, numbers, spaces, or service punctuation.",
  revenue: "Use dollars with up to two decimals.",
  expense: "Use dollars with up to two decimals.",
  date: "Choose a valid order date.",
  invoiceID: "Enter a valid invoice ID.",
};

const currency = new Intl.NumberFormat("en-CA", {
  style: "currency",
  currency: "CAD",
});

function phpPath(file) {
  const path = window.location.pathname;

  if (path.includes("/php/")) {
    return file;
  }

  if (path.includes("/html/")) {
    return `../php/${file}`;
  }

  return `php/${file}`;
}

function $(id) {
  return document.getElementById(id);
}

function setFieldError(fieldId, message = "") {
  const field = $(fieldId);
  const error = document.querySelector(`[data-for="${fieldId}"]`);

  if (field) {
    field.classList.toggle("invalid", Boolean(message));
  }

  if (error) {
    error.textContent = message;
  }
}

function clearErrors() {
  document.querySelectorAll(".field-error").forEach((node) => {
    node.textContent = "";
  });
  document.querySelectorAll(".invalid").forEach((node) => {
    node.classList.remove("invalid");
  });
}

function orderPayloadFromForm() {
  return {
    clientName: $("clientName")?.value.trim() ?? "",
    jobType: $("jobType")?.value.trim() ?? "",
    revenue: $("revenue")?.value.trim() ?? "",
    expense: $("expense")?.value.trim() ?? "",
    date: $("date")?.value ?? "",
    invoiceID: $("invoiceID")?.value.trim() ?? "",
  };
}

function validateOrder(mode) {
  clearErrors();
  const payload = orderPayloadFromForm();
  const errors = {};

  if ((mode === "update" || mode === "delete") && !patterns.invoiceID.test(payload.invoiceID)) {
    errors.invoiceID = fieldMessages.invoiceID;
  }

  if (mode !== "delete") {
    if (!patterns.clientName.test(payload.clientName)) {
      errors.clientName = fieldMessages.clientName;
    }
    if (!patterns.jobType.test(payload.jobType)) {
      errors.jobType = fieldMessages.jobType;
    }
    if (!patterns.money.test(payload.revenue)) {
      errors.revenue = fieldMessages.revenue;
    }
    if (!patterns.money.test(payload.expense)) {
      errors.expense = fieldMessages.expense;
    }
    if (!payload.date) {
      errors.date = fieldMessages.date;
    }
  }

  Object.entries(errors).forEach(([field, message]) => setFieldError(field, message));
  return Object.keys(errors).length === 0;
}

function setStatus(message, tone = "muted") {
  const status = $("formStatus");
  if (!status) return;

  status.textContent = message;
  status.className = tone;
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function applyServerErrors(fields = {}) {
  const fieldMap = {
    serviceType: "jobType",
    invoiceId: "invoiceID",
  };

  Object.entries(fields).forEach(([field, message]) => {
    setFieldError(fieldMap[field] || field, message);
  });
}

function setMode(mode) {
  const form = $("jobForm");
  const title = $("formTitle");
  const submit = $("submitOrder");
  const invoiceField = document.querySelector(".invoice-field");
  const loadButton = $("loadUpdateData");
  const editableFields = $("editableFields");

  if (!form || !title || !submit) return;

  clearErrors();
  setStatus("");
  form.dataset.mode = mode;
  $("currentMode").value = mode;

  document.querySelectorAll(".segmented-control button").forEach((button) => {
    button.classList.toggle("active", button.id === mode);
  });

  invoiceField?.classList.toggle("hidden", mode === "add");
  loadButton?.classList.toggle("hidden", mode !== "update");
  editableFields?.classList.toggle("hidden", mode === "delete");

  const labels = {
    add: ["Add Order", "Save Order"],
    update: ["Update Order", "Update Order"],
    delete: ["Delete Order", "Delete Order"],
  };

  title.textContent = labels[mode][0];
  submit.textContent = labels[mode][1];
}

async function postForm(endpoint, params) {
  const startedAt = performance.now();
  const response = await fetch(phpPath(endpoint), {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams(params).toString(),
  });

  const data = await response.json();
  data.latencyMs = Math.round(performance.now() - startedAt);

  if (!response.ok) {
    const error = new Error(data.error || "Request failed.");
    error.payload = data;
    throw error;
  }

  return data;
}

async function loadOrderForUpdate() {
  const invoiceID = $("invoiceID")?.value.trim();
  clearErrors();

  if (!patterns.invoiceID.test(invoiceID)) {
    setFieldError("invoiceID", fieldMessages.invoiceID);
    return;
  }

  setStatus("Loading order...");

  try {
    const data = await postForm("fetchEntry.php", { InvoiceID: invoiceID });
    $("clientName").value = data.Client_Name;
    $("jobType").value = data.Service_Type;
    $("revenue").value = data.Price;
    $("expense").value = data.Cost;
    $("date").value = data.Date;
    setStatus(`Loaded order #${data.InvoiceID}.`, "success");
  } catch (error) {
    applyServerErrors(error.payload?.fields);
    setStatus(error.message, "error");
  }
}

async function submitOrder(event) {
  event.preventDefault();

  const mode = $("jobForm").dataset.mode;
  if (!validateOrder(mode)) {
    setStatus("Please fix the highlighted fields.", "error");
    return;
  }

  const payload = orderPayloadFromForm();
  const endpointByMode = {
    add: "addfunctionality.php",
    update: "updatefunctionality.php",
    delete: "deletefunctionality.php",
  };

  const paramsByMode = {
    add: {
      clientName: payload.clientName,
      jobType: payload.jobType,
      revenue: payload.revenue,
      expense: payload.expense,
      date: payload.date,
    },
    update: {
      InvoiceID: payload.invoiceID,
      Client_Name: payload.clientName,
      Service_Type: payload.jobType,
      Price: payload.revenue,
      Cost: payload.expense,
      Date: payload.date,
    },
    delete: {
      InvoiceID: payload.invoiceID,
    },
  };

  setStatus("Saving...");

  try {
    const result = await postForm(endpointByMode[mode], paramsByMode[mode]);
    const latency = `${result.latencyMs}ms`;
    const suffix = mode === "add" ? ` Profit: ${result.profitFormatted}.` : "";
    setStatus(`${result.success} (${latency}).${suffix}`, "success");
    $("jobForm").reset();
    await loadSummary();
  } catch (error) {
    applyServerErrors(error.payload?.fields);
    setStatus(error.message, "error");
  }
}

function renderRecentOrders(orders = []) {
  const container = $("recentOrders");
  if (!container) return;

  if (!orders.length) {
    container.innerHTML = '<p class="empty-state">No orders yet.</p>';
    return;
  }

  container.innerHTML = orders
    .map((order) => {
      const profit = Number(order.Price) - Number(order.Cost);
      return `
        <article class="recent-order">
          <div>
            <strong>${escapeHtml(order.Client_Name)}</strong>
            <span>${escapeHtml(order.Service_Type)} &middot; ${escapeHtml(order.Date)}</span>
          </div>
          <b>${currency.format(profit)}</b>
        </article>
      `;
    })
    .join("");
}

async function loadSummary() {
  if (!$("totalRevenue")) return;

  try {
    const response = await fetch(phpPath("getSummary.php"));
    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.error || "Unable to load summary.");
    }

    $("totalRevenue").textContent = currency.format(data.totalRevenue);
    $("totalCost").textContent = currency.format(data.totalCost);
    $("totalProfit").textContent = currency.format(data.totalProfit);
    $("orderCount").textContent = data.orderCount;
    renderRecentOrders(data.latestOrders);
  } catch (error) {
    renderRecentOrders([]);
  }
}

async function loadUserGreeting() {
  const loginLink = $("login-btn");
  if (!loginLink) return;

  try {
    const response = await fetch(phpPath("getUser.php"));
    const data = await response.json();

    if (data.authenticated) {
      loginLink.textContent = `${data.firstName} ${data.lastName}`;
      loginLink.href = phpPath("logout.php");
      loginLink.title = "Log out";
    }
  } catch (error) {
    loginLink.textContent = "Login";
  }
}

document.addEventListener("DOMContentLoaded", () => {
  $("add")?.addEventListener("click", () => setMode("add"));
  $("update")?.addEventListener("click", () => setMode("update"));
  $("delete")?.addEventListener("click", () => setMode("delete"));
  $("loadUpdateData")?.addEventListener("click", loadOrderForUpdate);
  $("jobForm")?.addEventListener("submit", submitOrder);

  loadSummary();
  loadUserGreeting();
});
