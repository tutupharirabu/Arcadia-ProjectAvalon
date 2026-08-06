import { mount } from 'cypress/vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import { OhVueIcon } from 'oh-vue-icons'

import FormAuth from '../Login-Register/FormAuth.vue'

const pinia = createPinia()

const router = createRouter({
  history: createWebHistory(),
  routes: [{ path: '/', component: { template: '<div />' } }],
})

const mountFormAuth = (isRegister) => {
  cy.mount(FormAuth, {
    props: { isRegister },
    global: {
      plugins: [pinia, router],
      components: { 'v-icon': OhVueIcon },
    },
  })
}

describe('FormAuth', () => {
  it('menampilkan form login', () => {
    mountFormAuth(false)

    cy.get('input[type="email"]').should('be.visible')
    cy.get('input[type="password"]').should('be.visible')
    cy.contains('button', 'Log in').should('be.visible')
    cy.contains('Lupa Password?').should('be.visible')
  })

  it('menampilkan form register', () => {
    mountFormAuth(true)

    cy.get('input[placeholder="Isi Nama Lengkap"]').should('be.visible')
    cy.get('input[type="password"]').should('have.length', 2)
    cy.contains('button', 'Buat Akun').should('be.visible')
  })
})
