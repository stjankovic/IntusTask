<template>
    <div class="max-w-md mx-auto my-10 bg-gray-800 shadow-md rounded-lg p-8">
      <form @submit.prevent="submitUrl" class="space-y-4">
        <div>
          <label for="url" class="block text-sm font-medium text-gray-300">Enter URL:</label>
          <input type="text" id="url" v-model="originalUrl" required class="mt-1 block w-full text-black border border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-gray-700 sm:text-sm">
        </div>
        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-800 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
          Shorten URL
        </button>
        <p v-if="shortenedUrl" class="text-green-700 font-semibold">{{ shortenedUrl }}</p>
        <p v-if="error" class="text-red-400 font-semibold">{{ error }}</p>
      </form>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        original_url: '',
        shortenedUrl: '',
        error: ''
      };
    },
    methods: {
        async submitUrl() {
            try {
                const response = await axios.post('/submit-url', {
                original_url: this.originalUrl // Make sure this matches the expected key in Laravel
                });
                this.shortenedUrl = response.data.shortenedUrl;
            } catch (error) {
                this.error = error.response.data.message || 'An error occurred';
            }
        }
    }
  };
  </script>
  