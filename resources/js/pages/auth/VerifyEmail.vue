<template>
  <AuthPage
    :title="pageTitle"
    :description="pageDescription"
    :help-text="helpText"
  >
    <template #form>
      <!-- Verification Status -->
      <div class="text-center space-y-4">
        <!-- Loading State -->
        <div v-if="verifying" class="space-y-4">
          <div class="w-16 h-16 mx-auto">
            <Icon name="loading" :size="64" class="text-primary-600 animate-spin" />
          </div>
          <p class="text-gray-600 dark:text-gray-300">Verifying your email address...</p>
        </div>

        <!-- Success State -->
        <div v-else-if="verificationStatus === 'success'" class="space-y-4">
          <div class="w-16 h-16 mx-auto bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
            <Icon name="check" :size="32" class="text-green-600 dark:text-green-400" />
          </div>
          <div class="space-y-2">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Email Verified!</h3>
            <p class="text-gray-600 dark:text-gray-300">Your email address has been successfully verified.</p>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="verificationStatus === 'error'" class="space-y-4">
          <div class="w-16 h-16 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
            <Icon name="close" :size="32" class="text-red-600 dark:text-red-400" />
          </div>
          <div class="space-y-2">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Verification Failed</h3>
            <p class="text-gray-600 dark:text-gray-300">{{ errorMessage }}</p>
          </div>
        </div>

        <!-- Expired State -->
        <div v-else-if="verificationStatus === 'expired'" class="space-y-4">
          <div class="w-16 h-16 mx-auto bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
            <Icon name="clock" :size="32" class="text-orange-600 dark:text-orange-400" />
          </div>
          <div class="space-y-2">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Link Expired</h3>
            <p class="text-gray-600 dark:text-gray-300">This verification link has expired. Please request a new one.</p>
          </div>
        </div>

        <!-- Pending State (no token) -->
        <div v-else class="space-y-4">
          <div class="w-16 h-16 mx-auto bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
            <Icon name="mail" :size="32" class="text-blue-600 dark:text-blue-400" />
          </div>
          <div class="space-y-2">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Check Your Email</h3>
            <p class="text-gray-600 dark:text-gray-300">
              We've sent a verification link to your email address.
              <span v-if="email" class="block font-medium text-gray-900 dark:text-white mt-1">{{ email }}</span>
            </p>
          </div>

          <!-- Resend Section -->
          <div class="pt-4">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Didn't receive the email?</p>
            <button
              @click="resendVerification"
              :disabled="resendLoading || resendCooldown > 0"
              class="auth-button-ghost text-sm"
            >
              <span v-if="resendLoading" class="flex items-center justify-center">
                <Icon name="loading" :size="16" class="animate-spin mr-2" />
                Sending...
              </span>
              <span v-else-if="resendCooldown > 0">
                Resend in {{ resendCooldown }}s
              </span>
              <span v-else>Resend Verification Email</span>
            </button>
          </div>
        </div>

        <!-- Success Message -->
        <div v-if="resendMessage" class="auth-success">
          {{ resendMessage }}
        </div>
      </div>
    </template>

    <template #actions>
      <!-- Success Actions -->
      <div v-if="verificationStatus === 'success'" class="space-y-3">
        <button
          @click="continueToApp"
          class="auth-button-primary"
        >
          Continue to Dashboard
        </button>
      </div>

      <!-- Error Actions -->
      <div v-else-if="verificationStatus === 'error'" class="space-y-3">
        <button
          @click="resendVerification"
          :disabled="resendLoading"
          class="auth-button-primary"
        >
          <span v-if="resendLoading" class="flex items-center justify-center">
            <Icon name="loading" :size="20" class="animate-spin mr-2" />
            Sending New Link...
          </span>
          <span v-else>Send New Verification Link</span>
        </button>
      </div>

      <!-- Expired Actions -->
      <div v-else-if="verificationStatus === 'expired'" class="space-y-3">
        <button
          @click="resendVerification"
          :disabled="resendLoading"
          class="auth-button-primary"
        >
          <span v-if="resendLoading" class="flex items-center justify-center">
            <Icon name="loading" :size="20" class="animate-spin mr-2" />
            Sending New Link...
          </span>
          <span v-else>Request New Verification Link</span>
        </button>
      </div>
    </template>

    <template #links>
      <div class="space-y-2">
        <router-link :to="{ name: 'auth.login' }" class="auth-link block">
          Back to Sign In
        </router-link>
        <button @click="changeEmail" class="auth-link block">
          Wrong email address?
        </button>
      </div>
    </template>

    <template #footer>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Need help?
        <button @click="contactSupport" class="auth-link">
          Contact Support
        </button>
      </p>
    </template>
  </AuthPage>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AuthPage from '@/components/common/AuthPage.vue'
import Icon from '@/components/common/Icon.vue'

export default {
  name: 'VerifyEmail',
  components: {
    AuthPage,
    Icon,
  },
  setup() {
    const router = useRouter()
    const route = useRoute()
    const verifying = ref(false)
    const verificationStatus = ref(null) // null, 'success', 'error', 'expired'
    const errorMessage = ref('')
    const resendLoading = ref(false)
    const resendMessage = ref('')
    const resendCooldown = ref(0)
    const email = ref('')
    let resendTimer = null

    const pageTitle = computed(() => {
      switch (verificationStatus.value) {
        case 'success':
          return 'Email Verified'
        case 'error':
          return 'Verification Failed'
        case 'expired':
          return 'Link Expired'
        default:
          return 'Verify Your Email'
      }
    })

    const pageDescription = computed(() => {
      switch (verificationStatus.value) {
        case 'success':
          return 'Your email has been verified successfully. You can now access all features.'
        case 'error':
          return 'We couldn\'t verify your email address. Please try again.'
        case 'expired':
          return 'Your verification link has expired. We\'ll send you a new one.'
        default:
          return 'Click the verification link in your email to activate your account.'
      }
    })

    const helpText = computed(() => {
      switch (verificationStatus.value) {
        case 'success':
          return 'Your account is now fully activated and ready to use.'
        case 'error':
          return 'If you continue having issues, please contact our support team for assistance.'
        case 'expired':
          return 'Verification links expire after 24 hours for security reasons.'
        default:
          return 'Check your spam folder if you don\'t see the email. The link will expire in 24 hours.'
      }
    })

    const verifyEmail = async (token) => {
      verifying.value = true

      try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1500))

        // Mock verification logic
        if (!token) {
          verificationStatus.value = null
        } else if (token === 'expired-token') {
          verificationStatus.value = 'expired'
        } else if (token === 'invalid-token') {
          verificationStatus.value = 'error'
          errorMessage.value = 'This verification link is invalid or has already been used.'
        } else {
          // Success
          verificationStatus.value = 'success'
          console.log('Email verification successful for token:', token)
        }

      } catch (error) {
        verificationStatus.value = 'error'
        errorMessage.value = 'An unexpected error occurred during verification.'
        console.error('Verification error:', error)
      } finally {
        verifying.value = false
      }
    }

    const startResendCooldown = () => {
      resendCooldown.value = 60
      resendTimer = setInterval(() => {
        resendCooldown.value--
        if (resendCooldown.value <= 0) {
          clearInterval(resendTimer)
          resendTimer = null
        }
      }, 1000)
    }

    const resendVerification = async () => {
      resendLoading.value = true
      resendMessage.value = ''

      try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1000))

        resendMessage.value = `New verification email sent to ${email.value || 'your email address'}`
        startResendCooldown()
        console.log('Verification email resent')

      } catch (error) {
        resendMessage.value = 'Failed to send verification email. Please try again.'
        console.error('Resend error:', error)
      } finally {
        resendLoading.value = false
      }
    }

    const continueToApp = () => {
      router.push({ name: 'admin.dashboard' })
    }

    const changeEmail = () => {
      const newEmail = prompt('Enter your new email address:')
      if (newEmail && newEmail.includes('@')) {
        email.value = newEmail
        resendVerification()
      }
    }

    const contactSupport = () => {
      alert('Support: For email verification issues, please contact support@example.com')
    }

    // Initialize on mount
    onMounted(() => {
      const token = route.query.token
      email.value = route.query.email || 'user@example.com'

      if (token) {
        verifyEmail(token)
      } else {
        // No token - show pending verification state
        verificationStatus.value = null
      }
    })

    // Cleanup timer on unmount
    onUnmounted(() => {
      if (resendTimer) {
        clearInterval(resendTimer)
      }
    })

    return {
      verifying,
      verificationStatus,
      errorMessage,
      resendLoading,
      resendMessage,
      resendCooldown,
      email,
      pageTitle,
      pageDescription,
      helpText,
      resendVerification,
      continueToApp,
      changeEmail,
      contactSupport,
    }
  },
}
</script>