describe('Basic Drupal system checks.', () => {
  it('Administration checks.', () => {
    // Current user is an admin.
    cy.drush('tfa:reset-user --name=cypress -y');
    cy.login()
    cy.visit('/admin/people/role-settings')
    cy.get('#edit-user-admin-role').find('option:selected').should('contain', 'Administrator')
    // Memcache should be available.
    cy.visit('/admin/reports/memcache')
    cy.get('.block-system-main-block').should('contain', 'Uptime')
    // Basic Drupal performance options set.
    cy.visit('/admin/config/development/performance')
    cy.get('#edit-preprocess-css').should('be.checked')
    cy.get('#edit-preprocess-js').should('be.checked')
    cy.get('#edit-page-cache-maximum-age').find('option:selected').should('not.contain', 'no caching')
  })
})
