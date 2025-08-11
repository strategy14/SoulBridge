// ============================================
// -------------  VARIABLES  ------------------
// ============================================

// Sidebar
const menuItems = document.querySelectorAll(".menu-item");

// Messages
const messagesNotifications = document.querySelector("#messages-notifications");
const messages = document.querySelector(".messages");
const message = document.querySelectorAll(".message");
const messageSearch = document.querySelector("#message-search");

// Theme
const theme = document.querySelector("#theme");
const themeModel = document.querySelector(".customize-theme");

const fontSizes = document.querySelectorAll(".choose-size span");
const root = document.querySelector(":root");
const chooseColor = document.querySelectorAll(".choose-color span");

// Dark Theme
const bg1 = document.querySelector(".bg-1");
const bg2 = document.querySelector(".bg-2");
const bg3 = document.querySelector(".bg-3");

// ====================================== SIDEBAR ======================================

// Remove active class from menu items
const changeActiveItem = () => {
  menuItems.forEach((item) => item.classList.remove("active"));
};

menuItems.forEach((item) => {
  item.addEventListener("click", () => {
    changeActiveItem();
    item.classList.add("active");

    const notificationPopup = document.querySelector(".notification-popup");
    if (item.id !== "Notifications") {
      if (notificationPopup) notificationPopup.style.display = "none";
    } else {
      if (notificationPopup) {
        notificationPopup.style.display = "block";
        const notificationCount = item.querySelector(".notification-count");
        if (notificationCount) notificationCount.style.display = "none";
      }
    }
  });
});

// ====================================== MESSAGES ======================================

// Search messages
const searchMessage = () => {
  const val = messageSearch.value.toLowerCase();
  message.forEach((chat) => {
    let name = chat.querySelector("h5").textContent.toLowerCase();
    chat.style.display = name.includes(val) ? "flex" : "none";
  });
};

if (messageSearch) messageSearch.addEventListener("keyup", searchMessage);

// Highlight messages when clicked
if (messagesNotifications) {
  messagesNotifications.addEventListener("click", () => {
    if (messages) {
      messages.style.boxShadow = "0 0 1rem var(--color-primary)";
      setTimeout(() => (messages.style.boxShadow = "none"), 1500);
    }
    const notificationCount = messagesNotifications.querySelector(".notification-count");
    if (notificationCount) notificationCount.style.display = "none";
  });
}

// ====================================== THEME CUSTOMIZATION ======================================

const openThemeModel = () => {
  if (themeModel) themeModel.style.display = "grid";
};

const closeThemeModel = (e) => {
  if (e.target.classList.contains("customize-theme") && themeModel) {
    themeModel.style.display = "none";
  }
};

if (theme) theme.addEventListener("click", openThemeModel);
if (themeModel) themeModel.addEventListener("click", closeThemeModel);

// ===================================== FONTS ======================================

const removeActiveClass = () => {
  fontSizes.forEach((size) => size.classList.remove("active"));
};

fontSizes.forEach((size) => {
  size.addEventListener("click", () => {
    removeActiveClass();
    size.classList.add("active");

    let fontSize = "16px"; // Default size
    if (size.classList.contains("font-size-1")) fontSize = "10px";
    else if (size.classList.contains("font-size-2")) fontSize = "13px";
    else if (size.classList.contains("font-size-3")) fontSize = "16px";
    else if (size.classList.contains("font-size-4")) fontSize = "19px";
    else if (size.classList.contains("font-size-5")) fontSize = "22px";

    document.documentElement.style.fontSize = fontSize;
  });
});

// ===================================== COLOR THEME ======================================

const removeActive = () => {
  chooseColor.forEach((color) => color.classList.remove("active"));
};

chooseColor.forEach((color) => {
  color.addEventListener("click", () => {
    removeActive();
    color.classList.add("active");

    let primaryColor = "hsl(252, 75%, 60%)"; // Default color
    if (color.classList.contains("color-1")) primaryColor = "hsl(252, 75%, 60%)";
    else if (color.classList.contains("color-2")) primaryColor = "hsl(52, 75%, 60%)";
    else if (color.classList.contains("color-3")) primaryColor = "hsl(352, 75%, 60%)";
    else if (color.classList.contains("color-4")) primaryColor = "hsl(152, 75%, 60%)";
    else if (color.classList.contains("color-5")) primaryColor = "hsl(202, 75%, 60%)";

    root.style.setProperty("--color-primary", primaryColor);
  });
});

// ===================================== DARK THEME ======================================

const changeBG = () => {
  root.style.setProperty("--light-color-lightness", lightColorLightness);
  root.style.setProperty("--white-color-lightness", whiteColorLightness);
  root.style.setProperty("--dark-color-lightness", darkColorLightness);
};

[bg1, bg2, bg3].forEach((bg, index) => {
  bg.addEventListener("click", () => {
    whiteColorLightness = index === 1 ? "20%" : "10%";
    lightColorLightness = index === 1 ? "15%" : "0%";
    darkColorLightness = "95%";

    [bg1, bg2, bg3].forEach((b) => b.classList.remove("active"));
    bg.classList.add("active");
    changeBG();
  });
});

// ===================================== SEARCH SUGGESTIONS ======================================

const searchInput = document.querySelector('input[name="search"]');

if (searchInput) {
  searchInput.addEventListener("input", function (e) {
    const searchTerm = e.target.value;
    if (searchTerm.length > 2) {
      fetch(`search_suggestions.php?search=${encodeURIComponent(searchTerm)}`)
        .then((response) => response.json())
        .then((data) => showSuggestions(data));
    }
  });
}

// ===================================== IMAGE PREVIEW ======================================

