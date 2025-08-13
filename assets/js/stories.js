// Story functionality
class StoryViewer {
    constructor() {
        this.currentStoryIndex = 0;
        this.stories = [];
        this.modal = null;
        this.progressBar = null;
        this.timer = null;
        this.storyDuration = 5000; // 5 seconds per story
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.createModal();
    }
    
    bindEvents() {
        // Story click events
        document.addEventListener('click', (e) => {
            const storyElement = e.target.closest('.story[data-story-id]');
            if (storyElement) {
                e.preventDefault();
                const storyId = storyElement.dataset.storyId;
                this.openStory(storyId);
            }
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (this.modal && this.modal.style.display === 'flex') {
                switch(e.key) {
                    case 'Escape':
                        this.closeStory();
                        break;
                    case 'ArrowLeft':
                        this.previousStory();
                        break;
                    case 'ArrowRight':
                        this.nextStory();
                        break;
                }
            }
        });
    }
    
    createModal() {
        this.modal = document.createElement('div');
        this.modal.className = 'story-modal';
        this.modal.innerHTML = `
            <div class="story-viewer">
                <div class="story-header">
                    <div class="story-progress-container">
                        <div class="story-progress-bar"></div>
                    </div>
                    <div class="story-user-info">
                        <img src="" alt="User" class="story-user-avatar">
                        <span class="story-username"></span>
                        <span class="story-time"></span>
                    </div>
                    <button class="story-close-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="story-content">
                    <button class="story-nav-btn story-prev-btn">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="story-media-container">
                        <!-- Story media will be loaded here -->
                    </div>
                    <button class="story-nav-btn story-next-btn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(this.modal);
        
        // Bind modal events
        this.modal.querySelector('.story-close-btn').addEventListener('click', () => this.closeStory());
        this.modal.querySelector('.story-prev-btn').addEventListener('click', () => this.previousStory());
        this.modal.querySelector('.story-next-btn').addEventListener('click', () => this.nextStory());
        
        // Close on backdrop click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.closeStory();
            }
        });
        
        this.progressBar = this.modal.querySelector('.story-progress-bar');
    }
    
    async openStory(storyId) {
        try {
            // Fetch story data
            const response = await fetch(`/api/story/${storyId}`);
            if (!response.ok) {
                throw new Error('Failed to load story');
            }
            
            const storyData = await response.json();
            this.stories = storyData.stories || [storyData];
            this.currentStoryIndex = this.stories.findIndex(s => s.id == storyId);
            
            if (this.currentStoryIndex === -1) {
                this.currentStoryIndex = 0;
            }
            
            this.showStory();
            this.modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
        } catch (error) {
            console.error('Error loading story:', error);
            this.showErrorMessage('Failed to load story');
        }
    }
    
    showStory() {
        if (!this.stories.length) return;
        
        const story = this.stories[this.currentStoryIndex];
        const mediaContainer = this.modal.querySelector('.story-media-container');
        const userAvatar = this.modal.querySelector('.story-user-avatar');
        const username = this.modal.querySelector('.story-username');
        const storyTime = this.modal.querySelector('.story-time');
        
        // Update user info
        userAvatar.src = story.avatar || 'images/profile.jpg';
        username.textContent = `${story.firstName} ${story.lastName}`;
        storyTime.textContent = this.formatTime(story.created_at);
        
        // Clear previous media
        mediaContainer.innerHTML = '';
        
        // Create media element
        let mediaElement;
        if (story.mediaType === 'video') {
            mediaElement = document.createElement('video');
            mediaElement.controls = false;
            mediaElement.autoplay = true;
            mediaElement.muted = true;
            mediaElement.loop = false;
            mediaElement.addEventListener('ended', () => this.nextStory());
        } else {
            mediaElement = document.createElement('img');
        }
        
        mediaElement.src = story.media;
        mediaElement.className = 'story-media';
        mediaElement.alt = 'Story content';
        
        mediaElement.addEventListener('load', () => {
            this.startProgress();
        });
        
        mediaElement.addEventListener('error', () => {
            this.showErrorMessage('Failed to load media');
        });
        
        mediaContainer.appendChild(mediaElement);
        
        // Update navigation buttons
        const prevBtn = this.modal.querySelector('.story-prev-btn');
        const nextBtn = this.modal.querySelector('.story-next-btn');
        
        prevBtn.style.display = this.currentStoryIndex > 0 ? 'flex' : 'none';
        nextBtn.style.display = this.currentStoryIndex < this.stories.length - 1 ? 'flex' : 'none';
    }
    
    startProgress() {
        this.clearTimer();
        this.progressBar.style.width = '0%';
        
        let progress = 0;
        const increment = 100 / (this.storyDuration / 50);
        
        this.timer = setInterval(() => {
            progress += increment;
            this.progressBar.style.width = `${Math.min(progress, 100)}%`;
            
            if (progress >= 100) {
                this.nextStory();
            }
        }, 50);
    }
    
    clearTimer() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    }
    
    nextStory() {
        this.clearTimer();
        
        if (this.currentStoryIndex < this.stories.length - 1) {
            this.currentStoryIndex++;
            this.showStory();
        } else {
            this.closeStory();
        }
    }
    
    previousStory() {
        this.clearTimer();
        
        if (this.currentStoryIndex > 0) {
            this.currentStoryIndex--;
            this.showStory();
        }
    }
    
    closeStory() {
        this.clearTimer();
        this.modal.style.display = 'none';
        document.body.style.overflow = '';
        this.stories = [];
        this.currentStoryIndex = 0;
    }
    
    showErrorMessage(message) {
        const mediaContainer = this.modal.querySelector('.story-media-container');
        mediaContainer.innerHTML = `
            <div class="story-error">
                <i class="fas fa-exclamation-triangle"></i>
                <p>${message}</p>
            </div>
        `;
    }
    
    formatTime(timestamp) {
        const now = new Date();
        const storyTime = new Date(timestamp);
        const diffInHours = Math.floor((now - storyTime) / (1000 * 60 * 60));
        
        if (diffInHours < 1) {
            return 'Just now';
        } else if (diffInHours < 24) {
            return `${diffInHours}h ago`;
        } else {
            return storyTime.toLocaleDateString();
        }
    }
}

// Initialize story viewer when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new StoryViewer();
});

// Story upload functionality
function initializeStoryUpload() {
    const storyUploadInput = document.getElementById('storyUpload');
    if (storyUploadInput) {
        storyUploadInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Show preview before upload
                const reader = new FileReader();
                reader.onload = function(e) {
                    showStoryPreview(e.target.result, file.type);
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

function showStoryPreview(src, type) {
    const modal = document.createElement('div');
    modal.className = 'story-preview-modal';
    modal.innerHTML = `
        <div class="story-preview">
            <div class="preview-header">
                <h3>Story Preview</h3>
                <button class="close-preview">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="preview-content">
                ${type.startsWith('video') ? 
                    `<video src="${src}" controls class="preview-media"></video>` :
                    `<img src="${src}" class="preview-media">`
                }
            </div>
            <div class="preview-actions">
                <button class="btn-cancel">Cancel</button>
                <button class="btn-share">Share Story</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Bind events
    modal.querySelector('.close-preview').addEventListener('click', () => {
        document.body.removeChild(modal);
    });
    
    modal.querySelector('.btn-cancel').addEventListener('click', () => {
        document.body.removeChild(modal);
    });
    
    modal.querySelector('.btn-share').addEventListener('click', () => {
        // Submit the form
        document.querySelector('form[action="story-upload"]').submit();
        document.body.removeChild(modal);
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initializeStoryUpload);