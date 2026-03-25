/**
 * CustomCrypto - A lightweight encryption/decryption library
 * @author Deepak Maurya
 * @version 1.0.0
 * @license MIT
 */


const CustomCrypto = {
    /**
     * Encrypts a string using custom algorithm
     * @param {string} input - The string to encrypt
     * @returns {string} Encrypted hexadecimal string
     */
    encrypt: function(input) {
        let encrypted = "";
        
        for (let i = 0; i < input.length; i++) {
            const charCode = input.charCodeAt(i);
            const transformed = (charCode ^ 255) + i;
            encrypted += transformed.toString(16).padStart(2, '0');
        }
        
        return encrypted;
    },


    /**
     * Encrypts an object by JSON.stringify-ing it first
     * @param {object} obj - The object to encrypt
     * @returns {string} Encrypted hexadecimal string
     */
    encryptObject: function(obj) {
        const jsonString = JSON.stringify(obj);
        return this.encrypt(jsonString);
    },

    /**
     * Generates a random string that can be used as salt
     * @param {number} length - Length of the random string (default 16)
     * @returns {string} Random string
     */
    generateRandomString: function(length = 16) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';
        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }
};

// Export for different environments
if (typeof module !== 'undefined' && module.exports) {
    // Node.js
    module.exports = CustomCrypto;
} else if (typeof window !== 'undefined') {
    // Browser
    window.CustomCrypto = CustomCrypto;
}