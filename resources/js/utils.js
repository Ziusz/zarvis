/**
 * Format a number as currency
 * @param {number} amount - The amount to format
 * @param {string} [currency='USD'] - The currency code
 * @returns {string} The formatted currency string
 */
export function formatCurrency(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount);
}

/**
 * Format a date string
 * @param {string} date - The date string to format
 * @param {object} options - Intl.DateTimeFormat options
 * @returns {string} The formatted date string
 */
export function formatDate(date, options = {}) {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        ...options,
    }).format(new Date(date));
}

/**
 * Format a time duration in minutes to a human-readable string
 * @param {number} minutes - The duration in minutes
 * @returns {string} The formatted duration string
 */
export function formatDuration(minutes) {
    if (minutes < 60) {
        return `${minutes} min`;
    }
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;
    return remainingMinutes > 0 
        ? `${hours}h ${remainingMinutes}m`
        : `${hours}h`;
}

/**
 * Truncate a string to a maximum length
 * @param {string} str - The string to truncate
 * @param {number} length - The maximum length
 * @returns {string} The truncated string
 */
export function truncate(str, length = 100) {
    if (str.length <= length) return str;
    return str.slice(0, length) + '...';
}

/**
 * Generate initials from a name
 * @param {string} name - The full name
 * @returns {string} The initials
 */
export function getInitials(name) {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase();
}

/**
 * Check if a string is a valid email address
 * @param {string} email - The email address to validate
 * @returns {boolean} Whether the email is valid
 */
export function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Check if a string is a valid phone number
 * @param {string} phone - The phone number to validate
 * @returns {boolean} Whether the phone number is valid
 */
export function isValidPhone(phone) {
    const re = /^\+?[\d\s-()]{10,}$/;
    return re.test(phone);
}

/**
 * Format a phone number
 * @param {string} phone - The phone number to format
 * @returns {string} The formatted phone number
 */
export function formatPhone(phone) {
    // Remove all non-digits
    const digits = phone.replace(/\D/g, '');
    
    // Format based on length
    if (digits.length === 10) {
        return `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6)}`;
    }
    return phone;
}

/**
 * Generate a random string
 * @param {number} length - The length of the string
 * @returns {string} The random string
 */
export function randomString(length = 8) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    return Array.from({ length }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
} 