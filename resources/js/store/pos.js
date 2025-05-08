import { defineStore } from 'pinia'
import axios from 'axios'

export const usePOSStore = defineStore('pos', {
  state: () => ({
    items: [],
    cart: [],
    currentTransaction: null,
    error: null,
    subtotal: 0,
    tax: 0,
    total: 0,
    customers: [],
    transactions: []
  }),
  actions: {
    async fetchItems() {
        try {
            const response = await axios.get('/api/items')
            this.items = response.data.items
        } catch (error) {
            this.error = `Failed to fetch items: ${error.message}`
            console.error('Error details:', {
                message: error.message,
                response: error.response?.data,
                status: error.response?.status
            })
        }
    },

    async fetchCustomers() {
        try {
            const response = await axios.get('/api/customers')
            this.customers = response.data.customers
        } catch (error) {
            this.error = `Failed to fetch customers: ${error.message}`
            console.error('Error details:', {
                message: error.message,
                response: error.response?.data,
                status: error.response?.status
            })
        }
    },

    async fetchTransactions() {
        try {
          const response = await axios.get('/api/transactions')
          this.transactions = response.data.transactions
        } catch (error) {
          this.error = `Failed to fetch transactions: ${error.message}`
          console.error('Error details:', {
            message: error.message,
            response: error.response?.data,
            status: error.response?.status
          })
        }
      },

    addItemToCart(item) {
      const existingItem = this.cart.find(i => i.id === item.id);
      if (existingItem) {
        existingItem.quantity += 1;
      } else {
        this.cart.push({
          ...item,
          quantity: 1,
          unit_price: item.price
        });
      }
      this.calculateTotals();
    },

    decreaseQuantity(id) {
      const item = this.cart.find(i => i.id === id);
      if (item && item.quantity > 1) {
        item.quantity -= 1; // Decrease quantity
      } else if (item && item.quantity === 1) {
        this.removeItem(id); // If quantity is 1, remove the item
      }
      this.calculateTotals();
    },

    increaseQuantity(id) {
      const item = this.cart.find(i => i.id === id);
      if (item) {
        item.quantity += 1; // Increase quantity
      }
      this.calculateTotals();
    },

    removeItem(id) {
      this.cart = this.cart.filter(item => item.id !== id);
      this.calculateTotals();
    },

    calculateTotals() {
      this.subtotal = this.cart.reduce((total, item) => total + (item.unit_price * item.quantity), 0);
      this.tax = this.subtotal * 0.08;
      this.total = this.subtotal + this.tax;
    },

    clearCart() {
      this.cart = [];
      this.subtotal = 0;
      this.tax = 0;
      this.total = 0;
    },

    async submitTransaction(details) {
        try {
            const payload = {
                ...details,
                cart: this.cart,
                subtotal: this.subtotal,
                tax: this.tax,
                total: this.total,
                payment_method: details.payment_method || 'cash'
            };

            // If it's a card payment, include card details in the payload
            if (details.payment_method === 'Credit/Debit Card' && details.card_payment) {
                payload.card_payment = {
                    card_last_four: details.card_payment.card_last_four,
                    card_network: details.card_payment.card_network
                };
            }

            // Submit transaction
            const response = await axios.post('/api/transactions', payload);

            // If card payment was successful and have card details
            if (response.data.success && details.payment_method === 'Credit/Debit Card' && details.card_payment) {
                await axios.post('/api/card-payment', {
                    transaction_id: response.data.transaction_id,
                    card_last_four: details.card_payment.card_last_four,
                    card_network: details.card_payment.card_network
                });
            }

            return response.data;
        } catch (error) {
            console.error('Transaction Error:', {
                message: error.message,
                response: error.response?.data,
                status: error.response?.status,
                validationErrors: error.response?.data?.errors
            });
            throw error;
        }
    },

    preparePayment(transactionData) {
        this.currentTransaction = {
            ...transactionData,
            cart: [...this.cart],
            timestamp: new Date().toISOString()
        }
    },

    async completeTransaction(transactionData) {
        try {
            // First submit the transaction to backend
            await this.submitTransaction(transactionData);

            // After successful submission, clear the cart
            this.clearCart();

            // Clear the current transaction
            this.currentTransaction = null;

            return true;
        } catch (error) {
            console.error('Failed to complete transaction:', error);
            throw error;
        }
    },
  },
})
