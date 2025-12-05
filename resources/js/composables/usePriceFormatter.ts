import { ref, watch } from 'vue'

/**
 * Composable for formatting price inputs with dot separators
 * Example: 1000 -> 1.000, 150000 -> 150.000
 */
export function usePriceFormatter(initialValue: number | string = 0) {
  const displayValue = ref('')
  const numericValue = ref(0)

  /**
   * Format number to display with dot separators
   */
  const formatPrice = (value: number | string): string => {
    if (value === '' || value === null || value === undefined) {
      return ''
    }
    
    const num = typeof value === 'string' ? parseFloat(value) : value
    if (isNaN(num)) {
      return ''
    }
    
    // Format with dot as thousand separator
    return num.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.')
  }

  /**
   * Parse display value to numeric value
   */
  const parsePrice = (value: string): number => {
    if (!value) return 0
    
    // Remove dots and parse
    const cleaned = value.replace(/\./g, '')
    const num = parseFloat(cleaned)
    
    return isNaN(num) ? 0 : num
  }

  /**
   * Handle input event
   */
  const handleInput = (event: Event) => {
    const target = event.target as HTMLInputElement
    const value = target.value
    
    // Remove all non-digit characters except dots
    const cleaned = value.replace(/[^\d]/g, '')
    
    if (cleaned === '') {
      displayValue.value = ''
      numericValue.value = 0
      return
    }
    
    const num = parseFloat(cleaned)
    numericValue.value = num
    displayValue.value = formatPrice(num)
    
    // Set cursor position after formatting
    const cursorPosition = target.selectionStart || 0
    const oldLength = value.length
    const newLength = displayValue.value.length
    const diff = newLength - oldLength
    
    setTimeout(() => {
      target.setSelectionRange(cursorPosition + diff, cursorPosition + diff)
    }, 0)
  }

  /**
   * Set value programmatically
   */
  const setValue = (value: number | string) => {
    const num = typeof value === 'string' ? parseFloat(value) : value
    numericValue.value = isNaN(num) ? 0 : num
    displayValue.value = formatPrice(numericValue.value)
  }

  // Initialize
  setValue(initialValue)

  return {
    displayValue,
    numericValue,
    handleInput,
    setValue,
    formatPrice,
    parsePrice
  }
}

/**
 * Composable for formatting numeric inputs (quantity, stock, etc.) with dot separators
 * Supports decimal numbers
 * Example: 1000 -> 1.000, 150.5 -> 150,5, 1500.75 -> 1.500,75
 */
export function useNumberFormatter(initialValue: number | string = 0, allowDecimals: boolean = false) {
  const displayValue = ref('')
  const numericValue = ref(0)

  /**
   * Format number to display with dot separators and comma for decimals
   */
  const formatNumber = (value: number | string): string => {
    if (value === '' || value === null || value === undefined) {
      return ''
    }
    
    const num = typeof value === 'string' ? parseFloat(value.replace(',', '.')) : value
    if (isNaN(num)) {
      return ''
    }
    
    if (allowDecimals) {
      // Split into integer and decimal parts
      const parts = num.toString().split('.')
      const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.')
      const decimalPart = parts[1] || ''
      
      return decimalPart ? `${integerPart},${decimalPart}` : integerPart
    } else {
      // Integer only
      return Math.floor(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    }
  }

  /**
   * Parse display value to numeric value
   */
  const parseNumber = (value: string): number => {
    if (!value) return 0
    
    // Replace comma with dot for decimals, remove thousand separators
    const cleaned = value.replace(/\./g, '').replace(',', '.')
    const num = parseFloat(cleaned)
    
    return isNaN(num) ? 0 : num
  }

  /**
   * Handle input event
   */
  const handleInput = (event: Event) => {
    const target = event.target as HTMLInputElement
    const value = target.value
    
    // Remove all non-digit characters except dots and commas
    let cleaned = value.replace(/[^\d.,]/g, '')
    
    if (allowDecimals) {
      // Allow only one comma for decimal
      const commaParts = cleaned.split(',')
      if (commaParts.length > 2) {
        cleaned = commaParts[0] + ',' + commaParts.slice(1).join('')
      }
    } else {
      // Remove any commas or dots that aren't thousand separators
      cleaned = cleaned.replace(/[.,]/g, '')
    }
    
    if (cleaned === '' || cleaned === ',') {
      displayValue.value = ''
      numericValue.value = 0
      return
    }
    
    const num = parseNumber(cleaned)
    numericValue.value = num
    displayValue.value = formatNumber(num)
    
    // Set cursor position after formatting
    const cursorPosition = target.selectionStart || 0
    const oldLength = value.length
    const newLength = displayValue.value.length
    const diff = newLength - oldLength
    
    setTimeout(() => {
      target.setSelectionRange(cursorPosition + diff, cursorPosition + diff)
    }, 0)
  }

  /**
   * Set value programmatically
   */
  const setValue = (value: number | string) => {
    const num = typeof value === 'string' ? parseFloat(value.replace(',', '.')) : value
    numericValue.value = isNaN(num) ? 0 : num
    displayValue.value = formatNumber(numericValue.value)
  }

  // Initialize
  setValue(initialValue)

  return {
    displayValue,
    numericValue,
    handleInput,
    setValue,
    formatNumber,
    parseNumber
  }
}

/**
 * Simple formatter for displaying prices (read-only)
 */
export function formatPrice(value: number | string): string {
  if (value === '' || value === null || value === undefined) {
    return '0'
  }
  
  const num = typeof value === 'string' ? parseFloat(value) : value
  if (isNaN(num)) {
    return '0'
  }
  
  return num.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}

/**
 * Simple formatter for displaying numbers with dot separators (read-only)
 * Supports decimal numbers with comma as decimal separator
 */
export function formatNumber(value: number | string, decimals: number = 0): string {
  if (value === '' || value === null || value === undefined) {
    return '0'
  }
  
  const num = typeof value === 'string' ? parseFloat(value) : value
  if (isNaN(num)) {
    return '0'
  }
  
  if (decimals > 0) {
    const parts = num.toFixed(decimals).split('.')
    const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    const decimalPart = parts[1] || ''
    
    return decimalPart ? `${integerPart},${decimalPart}` : integerPart
  } else {
    return Math.floor(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
  }
}

