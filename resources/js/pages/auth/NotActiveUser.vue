<template>
      <Head title="Account Not Active" />
  <div class="flex flex-col items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 max-w-md w-full text-center">
      <div class="flex flex-col items-center mb-6">
        <div class="rounded-full bg-yellow-100 p-4 mb-4">
          <svg class="h-10 w-10 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Account Not Active</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
          Your account is currently in hiatus or has been locked by an administrator.<br>
          If you want your account to be active, please contact support.
        </p>
      </div>
      <a
        href="mailto:loanbram.noreply@gmail.com"
        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
      >
        Contact Support
      </a>
    <a
      href="/"
      class="mt-4 w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
    >
      Back to Home
    </a>
    </div>
  </div>
</template>
