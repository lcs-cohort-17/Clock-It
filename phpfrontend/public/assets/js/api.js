// API Service for backend communication
const api = {
    baseURL: window.APP_CONFIG?.API_BASE_URL || 'http://localhost:8000',
    
    async request(endpoint, options = {}) {
        const token = localStorage.getItem('token')
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...options.headers
        }
        
        if (token) {
            headers['Authorization'] = `Bearer ${token}`
        }
        
        try {
            const response = await fetch(`${this.baseURL}${endpoint}`, {
                ...options,
                headers
            })
            
            const data = await response.json()
            
            if (!response.ok) {
                throw new Error(data.error || data.message || 'Request failed')
            }
            
            return data
        } catch (error) {
            console.error('API Error:', error)
            throw error
        }
    },
    
    get(endpoint) {
        return this.request(endpoint, { method: 'GET' })
    },
    
    post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        })
    },
    
    patch(endpoint, data) {
        return this.request(endpoint, {
            method: 'PATCH',
            body: JSON.stringify(data)
        })
    },
    
    delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' })
    }
}

// Global helper for views
window.api = api