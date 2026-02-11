/**
 * NO BED SYNDROME - Main JavaScript
 * Real-Time Hospital Bed Availability System
 */

// Configuration
const API_BASE_URL = 'api/';
const REFRESH_INTERVAL = 30000; // 30 seconds

// Global state
let currentSlide = 0;
let heroImages = [];
let hospitals = [];
let autoRefreshInterval = null;

// ==================== UTILITY FUNCTIONS ====================

/**
 * Fetch data from API
 */
async function fetchAPI(endpoint) {
    try {
        const response = await fetch(API_BASE_URL + endpoint);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API Error:', error);
        showNotification('Error loading data. Please try again.', 'error');
        return null;
    }
}

/**
 * Show notification to user
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : '#0066cc'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

/**
 * Format date/time
 */
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Get status class based on availability percentage
 */
function getStatusClass(available, total) {
    const percentage = (available / total) * 100;
    if (percentage >= 30) return 'available';
    if (percentage >= 10) return 'limited';
    return 'full';
}

/**
 * Get status text
 */
function getStatusText(status) {
    const statusTexts = {
        'available': 'Available',
        'limited': 'Limited',
        'full': 'Full'
    };
    return statusTexts[status] || 'Unknown';
}

// ==================== NAVIGATION ====================

/**
 * Initialize navigation
 */
function initNavigation() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.navbar-menu');
    
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
        
        // Close menu when clicking a link
        navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
            });
        });
    }
    
    // Highlight active page
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    document.querySelectorAll('.navbar-menu a').forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });
}

// ==================== HERO MARQUEE ====================

/**
 * Load and initialize hero marquee
 */
async function initHeroMarquee() {
    const response = await fetchAPI('get_hero_images.php');
    
    if (response && response.success && response.data) {
        heroImages = response.data.images || [];
        renderMarquee();
        startMarquee();
    }
}

/**
 * Render marquee slides
 */
function renderMarquee() {
    const container = document.querySelector('.marquee-container');
    if (!container) return;
    
    container.innerHTML = heroImages.map((image, index) => `
        <div class="marquee-slide ${index === 0 ? 'active' : ''}">
            <img src="${image.image_url}" alt="${image.caption || 'Hospital care'}">
            <div class="marquee-caption">${image.caption || ''}</div>
        </div>
    `).join('');
}

/**
 * Start marquee auto-rotation
 */
function startMarquee() {
    setInterval(() => {
        const slides = document.querySelectorAll('.marquee-slide');
        if (slides.length === 0) return;
        
        slides[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('active');
    }, 5000);
}

// ==================== HOSPITAL DIRECTORY ====================

/**
 * Load and display hospitals
 */
async function loadHospitals() {
    const response = await fetchAPI('get_hospitals.php');
    
    if (response && response.success && response.data) {
        hospitals = response.data.hospitals || [];
        renderHospitals();
    }
}

/**
 * Render hospitals in directory
 */
function renderHospitals() {
    const container = document.getElementById('hospitals-list');
    if (!container) return;
    
    if (hospitals.length === 0) {
        container.innerHTML = '<p class="text-center">No hospitals found.</p>';
        return;
    }
    
    container.innerHTML = hospitals.map(hospital => `
        <div class="hospital-item" onclick="viewHospitalDetails(${hospital.hospital_id})">
            <img src="${hospital.logo_url || 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjZjNmNGY2Ii8+CjxwYXRoIGQ9Ik0xMDAgNDBMMTQwIDgwVjE0MEgxMzBMMTAwIDEwMEw3MCAxNDBINjBWODBMMTAwIDQwWiIgZmlsbD0iIzY5NzM4NCIvPgo8Y2lyY2xlIGN4PSIxMDAiIGN5PSIxMDAiIHI9IjMwIiBmaWxsPSIjZmZmZmZmIi8+Cjx0ZXh0IHg9IjEwMCIgeT0iMTEwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjNjk3Mzg0IiBmb250LXNpemU9IjI0IiBmb250LWZhbWlseT0iQXJpYWwiPkgiPC90ZXh0Pgo8L3N2Zz4='}" 
                 alt="${hospital.name}" 
                 class="hospital-logo"
                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjZjNmNGY2Ii8+CjxwYXRoIGQ9Ik0xMDAgNDBMMTQwIDgwVjE0MEgxMzBMMTAwIDEwMEw3MCAxNDBINjBWODBMMTAwIDQwWiIgZmlsbD0iIzY5NzM4NCIvPgo8Y2lyY2xlIGN4PSIxMDAiIGN5PSIxMDAiIHI9IjMwIiBmaWxsPSIjZmZmZmZmIi8+Cjx0ZXh0IHg9IjEwMCIgeT0iMTEwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjNjk3Mzg0IiBmb250LXNpemU9IjI0IiBmb250LWZhbWlseT0iQXJpYWwiPkgiPC90ZXh0Pgo8L3N2Zz4='">
            
            <div class="hospital-info">
                <h3>${hospital.name}</h3>
                <p>${hospital.address}</p>
                <div class="hospital-meta">
                    <span>📍 ${hospital.region}</span>
                    <span>📞 ${hospital.phone}</span>
                    <span>✉️ ${hospital.email}</span>
                    <span>🛏️ ${hospital.total_beds} total beds</span>
                </div>
            </div>
            
            <div class="hospital-actions">
                <span class="status-badge ${hospital.status}">
                    ${getStatusText(hospital.status)}
                </span>
                <small style="color: var(--medium-gray);">
                    ${hospital.available_beds} beds available
                </small>
            </div>
        </div>
    `).join('');
}

/**
 * View hospital details
 */
function viewHospitalDetails(hospitalId) {
    window.location.href = `hospital_details.html?id=${hospitalId}`;
}

// ==================== BED STATUS DASHBOARD ====================

/**
 * Load bed availability status
 */
async function loadBedStatus() {
    const response = await fetchAPI('get_bed_status.php');
    
    if (response && response.success && response.data) {
        renderBedStatus(response.data);
        updateLastRefreshTime();
    }
}

/**
 * Render bed status dashboard
 */
function renderBedStatus(data) {
    // Render summary
    const summaryContainer = document.getElementById('bed-status-summary');
    if (summaryContainer && data.summary) {
        summaryContainer.innerHTML = `
            <div class="bed-status-card">
                <div class="bed-status-header">
                    <div class="bed-count">
                        <span class="bed-count-number">${data.summary.total_available}</span>
                        <span class="bed-count-label">Total Available</span>
                    </div>
                </div>
                <div class="bed-types">
                    <div class="bed-type">
                        <div class="bed-type-count">${data.summary.icu_available}</div>
                        <div class="bed-type-label">ICU</div>
                    </div>
                    <div class="bed-type">
                        <div class="bed-type-count">${data.summary.emergency_available}</div>
                        <div class="bed-type-label">Emergency</div>
                    </div>
                    <div class="bed-type">
                        <div class="bed-type-count">${data.summary.total_available - data.summary.icu_available - data.summary.emergency_available}</div>
                        <div class="bed-type-label">General</div>
                    </div>
                </div>
            </div>
            
            <div class="bed-status-card">
                <h4>Hospital Status</h4>
                <div class="bed-types">
                    <div class="bed-type">
                        <div class="bed-type-count" style="color: var(--status-available);">${data.summary.hospitals_available}</div>
                        <div class="bed-type-label">Available</div>
                    </div>
                    <div class="bed-type">
                        <div class="bed-type-count" style="color: var(--status-limited);">${data.summary.hospitals_limited}</div>
                        <div class="bed-type-label">Limited</div>
                    </div>
                    <div class="bed-type">
                        <div class="bed-type-count" style="color: var(--status-full);">${data.summary.hospitals_full}</div>
                        <div class="bed-type-label">Full</div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Render hospital bed status
    const hospitalsContainer = document.getElementById('hospitals-bed-status');
    if (hospitalsContainer && data.hospitals) {
        hospitalsContainer.innerHTML = data.hospitals.map(hospital => `
            <div class="bed-status-card">
                <h4>${hospital.name}</h4>
                <div class="hospital-meta mb-2">
                    <span>📍 ${hospital.region}</span>
                    <span class="status-badge ${hospital.status}">
                        ${getStatusText(hospital.status)}
                    </span>
                </div>
                
                <div class="bed-count">
                    <span class="bed-count-number">${hospital.available_beds}/${hospital.total_beds}</span>
                    <span class="bed-count-label">Available Beds</span>
                </div>
                
                <div class="bed-types">
                    <div class="bed-type">
                        <div class="bed-type-count">${hospital.icu_available}/${hospital.icu_beds}</div>
                        <div class="bed-type-label">ICU</div>
                        <span class="status-badge ${hospital.icu_status}" style="font-size: 0.7rem; padding: 0.25rem 0.5rem; margin-top: 0.25rem;">
                            ${getStatusText(hospital.icu_status)}
                        </span>
                    </div>
                    <div class="bed-type">
                        <div class="bed-type-count">${hospital.emergency_available}/${hospital.emergency_beds}</div>
                        <div class="bed-type-label">Emergency</div>
                        <span class="status-badge ${hospital.emergency_status}" style="font-size: 0.7rem; padding: 0.25rem 0.5rem; margin-top: 0.25rem;">
                            ${getStatusText(hospital.emergency_status)}
                        </span>
                    </div>
                    <div class="bed-type">
                        <div class="bed-type-count">${hospital.general_available}/${hospital.general_beds}</div>
                        <div class="bed-type-label">General</div>
                    </div>
                </div>
                
                <small style="color: var(--medium-gray); display: block; margin-top: 1rem;">
                    Updated: ${formatDateTime(hospital.updated_at)}
                </small>
            </div>
        `).join('');
    }
}

/**
 * Update last refresh time
 */
function updateLastRefreshTime() {
    const element = document.getElementById('last-refresh');
    if (element) {
        element.textContent = `Last updated: ${new Date().toLocaleTimeString()}`;
    }
}

/**
 * Start auto-refresh for bed status
 */
function startAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    
    autoRefreshInterval = setInterval(() => {
        loadBedStatus();
    }, REFRESH_INTERVAL);
}

// ==================== HOSPITAL DETAILS PAGE ====================

/**
 * Load hospital details
 */
async function loadHospitalDetails() {
    const urlParams = new URLSearchParams(window.location.search);
    const hospitalId = urlParams.get('id');
    
    if (!hospitalId) {
        showNotification('Invalid hospital ID', 'error');
        setTimeout(() => window.location.href = 'hospitals.html', 2000);
        return;
    }
    
    const response = await fetchAPI(`get_hospital_details.php?id=${hospitalId}`);
    
    if (response && response.success && response.data) {
        renderHospitalDetails(response.data);
    }
}

/**
 * Render hospital details
 */
function renderHospitalDetails(hospital) {
    // Update page title
    document.title = `${hospital.name} - No Bed Syndrome`;
    
    // Render hospital info
    const infoContainer = document.getElementById('hospital-info');
    if (infoContainer) {
        infoContainer.innerHTML = `
            <h1>${hospital.name}</h1>
            <div class="hospital-meta mb-3">
                <span>📍 ${hospital.address}, ${hospital.region}</span>
                <span>📞 ${hospital.phone}</span>
                <span>✉️ ${hospital.email}</span>
            </div>
            <div class="mb-3">
                <span class="status-badge ${hospital.status}">
                    ${getStatusText(hospital.status)} - ${hospital.available_beds}/${hospital.total_beds} beds available
                </span>
            </div>
        `;
    }
    
    // Render image gallery
    const galleryContainer = document.getElementById('image-gallery');
    if (galleryContainer && hospital.images && hospital.images.length > 0) {
        galleryContainer.innerHTML = hospital.images.map(image => `
            <img src="${image.image_url}" 
                 alt="${image.caption || hospital.name}" 
                 class="card-image"
                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjUwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDI1MCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyNTAiIGhlaWdodD0iMjAwIiBmaWxsPSIjZjNmNGY2Ii8+CjxwYXRoIGQ9Ik0xMjUgNDBMMTY1IDgwVjE0MEgxNTVMMTI1IDEwMEw5NSAxNDBIOTBWOEJMMTI1IDQwWiIgZmlsbD0iIzY5NzM4NCIvPgo8Y2lyY2xlIGN4PSIxMjUiIGN5PSIxMDAiIHI9IjMwIiBmaWxsPSIjZmZmZmZmIi8+Cjx0ZXh0IHg9IjEyNSIgeT0iMTEwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjNjk3Mzg0IiBmb250LXNpemU9IjI0IiBmb250LWZhbWlseT0iQXJpYWwiPkgiPC90ZXh0Pgo8L3N2Zz4='">
        `).join('');
    }
    
    // Render bed status
    const bedStatusContainer = document.getElementById('bed-status-details');
    if (bedStatusContainer) {
        bedStatusContainer.innerHTML = `
            <div class="bed-status-grid">
                <div class="bed-status-card">
                    <h4>Total Beds</h4>
                    <div class="bed-count">
                        <span class="bed-count-number">${hospital.available_beds}/${hospital.total_beds}</span>
                        <span class="bed-count-label">Available</span>
                    </div>
                </div>
                <div class="bed-status-card">
                    <h4>ICU Beds</h4>
                    <div class="bed-count">
                        <span class="bed-count-number">${hospital.icu_available}/${hospital.icu_beds}</span>
                        <span class="bed-count-label">Available</span>
                    </div>
                    <span class="status-badge ${hospital.icu_status} mt-2">
                        ${getStatusText(hospital.icu_status)}
                    </span>
                </div>
                <div class="bed-status-card">
                    <h4>Emergency Beds</h4>
                    <div class="bed-count">
                        <span class="bed-count-number">${hospital.emergency_available}/${hospital.emergency_beds}</span>
                        <span class="bed-count-label">Available</span>
                    </div>
                    <span class="status-badge ${hospital.emergency_status} mt-2">
                        ${getStatusText(hospital.emergency_status)}
                    </span>
                </div>
            </div>
        `;
    }
    
    // Render doctors
    const doctorsContainer = document.getElementById('doctors-list');
    if (doctorsContainer && hospital.doctors && hospital.doctors.length > 0) {
        doctorsContainer.innerHTML = hospital.doctors.map(doctor => `
            <div class="card">
                <img src="${doctor.photo_url || 'assets/doctors/default.jpg'}" 
                     alt="${doctor.name}" 
                     class="card-image"
                     onerror="this.src='assets/doctors/default.jpg'">
                <div class="card-body">
                    <h4 class="card-title">${doctor.name}</h4>
                    <p><strong>Specialty:</strong> ${doctor.specialty}</p>
                    ${doctor.biography ? `<p>${doctor.biography}</p>` : ''}
                    ${doctor.years_experience ? `<p><strong>Experience:</strong> ${doctor.years_experience} years</p>` : ''}
                </div>
                <div class="card-footer">
                    ${doctor.email ? `<p>✉️ ${doctor.email}</p>` : ''}
                    ${doctor.phone ? `<p>📞 ${doctor.phone}</p>` : ''}
                </div>
            </div>
        `).join('');
    }
    
    // Render departments
    const departmentsContainer = document.getElementById('departments-list');
    if (departmentsContainer && hospital.departments && hospital.departments.length > 0) {
        departmentsContainer.innerHTML = hospital.departments.map(dept => `
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">${dept.name}</h4>
                    ${dept.description ? `<p class="card-text">${dept.description}</p>` : ''}
                    ${dept.head_of_department ? `<p><strong>Head:</strong> ${dept.head_of_department}</p>` : ''}
                </div>
                ${dept.contact_phone || dept.contact_email ? `
                    <div class="card-footer">
                        ${dept.contact_phone ? `<p>📞 ${dept.contact_phone}</p>` : ''}
                        ${dept.contact_email ? `<p>✉️ ${dept.contact_email}</p>` : ''}
                    </div>
                ` : ''}
            </div>
        `).join('');
    }
    
    // Render services
    const servicesContainer = document.getElementById('services-list');
    if (servicesContainer && hospital.services && hospital.services.length > 0) {
        servicesContainer.innerHTML = `
            <div class="card">
                <div class="card-body">
                    <h4>Available Services</h4>
                    <ul style="list-style: none; padding: 0;">
                        ${hospital.services.map(service => `
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--light-gray);">
                                <strong>${service.service_name}</strong>
                                ${service.description ? `<br><small>${service.description}</small>` : ''}
                            </li>
                        `).join('')}
                    </ul>
                </div>
            </div>
        `;
    }
}

// ==================== INITIALIZATION ====================

/**
 * Initialize the application based on current page
 */
document.addEventListener('DOMContentLoaded', () => {
    initNavigation();
    
    const currentPage = window.location.pathname.split('/').pop();
    
    // Home page
    if (currentPage === '' || currentPage === 'index.html') {
        initHeroMarquee();
    }
    
    // Hospitals directory page
    if (currentPage === 'hospitals.html') {
        loadHospitals();
    }
    
    // Bed status page
    if (currentPage === 'bed_status.html') {
        loadBedStatus();
        startAutoRefresh();
    }
    
    // Hospital details page
    if (currentPage === 'hospital_details.html') {
        loadHospitalDetails();
    }
});

// Clean up on page unload
window.addEventListener('beforeunload', () => {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
});
