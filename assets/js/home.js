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

const imageInput = document.querySelector('input[type="file"]');

function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const previewVideo = document.getElementById('previewVideo');
    const file = input.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (file.type.startsWith('video/')) {
                previewVideo.src = e.target.result;
                previewVideo.style.display = 'block';
                previewImg.style.display = 'none';
            } else {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
                previewVideo.style.display = 'none';
            }
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
}

// Close preview functionality
if (document.getElementById('closePreview')) {
    document.getElementById('closePreview').addEventListener('click', function() {
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const previewVideo = document.getElementById('previewVideo');
        const inputFile = document.getElementById('imagefile');

        previewImg.src = '';
        previewVideo.src = '';
        preview.style.display = 'none';
        inputFile.value = '';
    });
}

// ===================================== LIKE FUNCTIONALITY ======================================

document.addEventListener('DOMContentLoaded', function() {
    // Handle like button clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.like-btn')) {
            e.preventDefault();
            const likeBtn = e.target.closest('.like-btn');
            const postId = likeBtn.closest('.feed').dataset.postId;
            const heartIcon = likeBtn.querySelector('i');
            const likeCount = likeBtn.querySelector('.like-count i');
            
            const isLiked = heartIcon.classList.contains('fa-solid');
            const action = isLiked ? 'unlike' : 'like';
            
            fetch('/like', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    post_id: parseInt(postId),
                    action: action
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update heart icon
                    if (data.liked) {
                        heartIcon.classList.remove('fa-regular');
                        heartIcon.classList.add('fa-solid');
                        heartIcon.style.color = 'red';
                    } else {
                        heartIcon.classList.remove('fa-solid');
                        heartIcon.classList.add('fa-regular');
                        heartIcon.style.color = '';
                    }
                    
                    // Update like count
                    likeCount.textContent = data.like_count;
                } else {
                    console.error('Like action failed:', data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });
    
    // Handle comment form submissions
    document.addEventListener('submit', function(e) {
        if (e.target.classList.contains('comment-form')) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            
            fetch('/comment', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    form.reset();
                    // Optionally reload comments or update count
                    location.reload();
                } else {
                    console.error('Comment submission failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Fallback to normal form submission
                form.submit();
            });
        }
    });
});

// ===================================== STORY FUNCTIONALITY ======================================

function initializeStories() {
    const storiesContainer = document.querySelector('.stories');
    if (storiesContainer) {
        // Make stories horizontally scrollable
        storiesContainer.style.overflowX = 'auto';
        storiesContainer.style.scrollBehavior = 'smooth';
        
        // Add click handlers for story viewing
        const storyItems = document.querySelectorAll('.story');
        storyItems.forEach(story => {
            story.addEventListener('click', function() {
                const storyId = this.dataset.storyId;
                if (storyId) {
                    viewStory(storyId);
                }
            });
        });
    }
}

function viewStory(storyId) {
    // Create story viewer modal
    const modal = document.createElement('div');
    modal.className = 'story-modal';
    modal.innerHTML = `
        <div class="story-viewer">
            <div class="story-header">
                <div class="story-progress"></div>
                <button class="close-story">&times;</button>
            </div>
            <div class="story-content">
                <div class="story-media-container">
                    <!-- Story content will be loaded here -->
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Close story functionality
    modal.querySelector('.close-story').addEventListener('click', function() {
        document.body.removeChild(modal);
    });
    
    // Load story content (you can implement this based on your needs)
    loadStoryContent(storyId, modal.querySelector('.story-media-container'));
}

function loadStoryContent(storyId, container) {
    // This would typically fetch story data from the server
    // For now, we'll just show a placeholder
    container.innerHTML = '<p>Story content loading...</p>';
}

// Initialize stories when page loads
document.addEventListener('DOMContentLoaded', initializeStories);