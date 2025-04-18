export const calculateOriginalSubtotal = cartItems => {
  return cartItems.reduce((total, item) => {
    return total + parseFloat(item.c_price || item.price)
  }, 0)
}


export const calculateTotalDiscount = cartItems => {
  return cartItems.reduce((total, item) => {
    const itemPrice = parseFloat(item.c_price || item.price)
    const discountPercentage = parseFloat(item.c_discount || item.discount || 0)
    const discountAmount = itemPrice * (discountPercentage / 100)
    
    return total + discountAmount
  }, 0)
}


export const calculateFinalTotal = (subtotal, discount) => {
  return subtotal - discount
} 

/**
 * Calculate the discounted price for a single item
 * @param {Object} item - Cart item with price and discount information
 * @returns {number} - The discounted price
 */
export const calculateDiscountedPrice = item => {
  const originalPrice = parseFloat(item.c_price || item.price || 0)
  const discountPercentage = parseFloat(item.c_discount || item.discount || item.game?.g_discount || 0)
  
  if (discountPercentage <= 0) {
    return originalPrice
  }
  
  const discountAmount = originalPrice * (discountPercentage / 100)
  
  return originalPrice - discountAmount
} 
