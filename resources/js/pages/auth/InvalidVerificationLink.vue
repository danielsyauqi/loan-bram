<template>
<Head title="Invalid Verification Link" />
  <div class="invalid-link-page">
    <div class="invalid-link-container">
      <div class="flex justify-center">
        <img src="/loanbram-black.png" alt="Loan Bram Logo" class="invalid-link-logo" />
      </div>
      <h1 class="invalid-link-title">Invalid or Expired Link</h1>
      <p class="invalid-link-message">
        The email verification link you used is invalid or has expired.<br>
        Please restart the registration process to receive a new verification email.
      </p>
      <Button class="invalid-link-btn" @click="goToRegister">Restart Registration</Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useRouter } from 'vue-router';

declare function route(name: string, params?: any): string;
const router = useRouter();

const goToRegister = () => {
  window.location.href = route('register');
};
</script>

<style scoped>
.invalid-link-page {
  min-height: 100vh;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
}
.invalid-link-container {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 4px 24px rgba(30,41,59,0.08);
  padding: 2.5rem 2rem 2rem 2rem;
  max-width: 400px;
  text-align: center;
}
.invalid-link-logo {
  max-width: 100px;
  margin-bottom: 1.5rem;
}
.invalid-link-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.75rem;
}
.invalid-link-message {
  color: #334155;
  font-size: 1.05rem;
  margin-bottom: 2rem;
}
.invalid-link-btn {
  background: #0f172a;
  color: #fff;
  font-weight: 600;
  border-radius: 0.5rem;
  padding: 0.75rem 2rem;
  font-size: 1rem;
  transition: background 0.2s;
}
.invalid-link-btn:hover {
  background: #1e293b;
}
</style> 