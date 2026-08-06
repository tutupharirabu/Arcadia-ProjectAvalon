// Test e2e nyata untuk halaman landing dan halaman login.

describe('Arcadia Landing Page', () => {
  it('menampilkan halaman beranda dengan konten utama', () => {
    cy.visit('/beranda')

    cy.contains('h1', 'Berkebun Cerdas').should('be.visible')
    cy.contains('Tentang Kami').should('be.visible')
    cy.contains('Daftar Arcadia Partner').should('be.visible')
  })

  it('mengarahkan route tidak dikenal ke beranda', () => {
    cy.visit('/halaman-tidak-ada')
    cy.contains('h1', 'Berkebun Cerdas').should('be.visible')
  })
})

describe('Login Petani', () => {
  it('menampilkan form login', () => {
    cy.visit('/monitoring-arcadia/login')

    cy.get('input[type="email"]').should('be.visible')
    cy.get('input[type="password"]').should('be.visible')
    cy.contains('button', 'Log in').should('be.visible')
    cy.contains('Lupa Password?').should('be.visible')
  })
})
