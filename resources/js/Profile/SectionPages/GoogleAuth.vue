<template>
  <div>
    <div v-if="!isLoggedIn">
      <button @click="login" class="btn btn-sm btn-primary">
        <span class="d-flex align-items-center">
          <img src="/img/logo/google.svg" class="mr-1" alt="Google" />
          {{$t('settings.lbl_connect_google')}}
        </span>
      </button>
      <p style="font-weight: 500; margin-top: 15px">{{$t('settings.lbl_google_text')}}</p>
    </div>
    <div v-else>
      <div class="d-flex justify-content-between flex-wrap gap-3" style="margin: 30px 15px">
        <p style="font-weight: 500">{{$t('settings.lbl_success_google')}}</p>
        <button @click="logout" class="btn btn-sm btn-primary">{{$t('settings.lbl_disconnect')}}</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      isLoggedIn: false,
      userDetails: null
    }
  },
  mounted() {
    this.checkLoginStatus()
  },
  methods: {
    checkLoginStatus() {
      const storedLogin = localStorage.getItem('isLoggedIn')
      if (storedLogin) {
        const storedTokenExpiry = localStorage.getItem('tokenExpiry')
        const tokenExpiry = new Date(storedTokenExpiry)
        const now = new Date()

        if (tokenExpiry && tokenExpiry > now) {
          this.isLoggedIn = true
        } else {
          this.isLoggedIn = false
          localStorage.removeItem('isLoggedIn')
          localStorage.removeItem('userDetails')
        }
      }
    },
    async login() {
      try {
        const response = await axios.post('/auth/google')
        const authUrl = response.data
        
        window.location.href = authUrl
      } catch (error) {
        console.error('Error initiating Google OAuth:', error)
      }
    },
    async logout() {
      try {
        await axios.post('token-revoke')
        localStorage.removeItem('isLoggedIn')
        localStorage.removeItem('tokenExpiry')
        this.isLoggedIn = false
      } catch (error) {
        console.error('Logout failed:', error)
      }
    }
  }
}
</script>
