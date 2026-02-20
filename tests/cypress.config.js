const { defineConfig } = require("cypress");
const { downloadFile } = require("cypress-downloadfile/lib/addPlugin");

module.exports = defineConfig({
  reporter: "cypress-multi-reporters",

  reporterOptions: {
    configFile: "reporter-config.json",
  },

  video: true,
  retries: {
    // Configure retry attempts for `cypress run`
    runMode: 2,
    // Configure retry attempts for `cypress open`
    openMode: 0
  },

  e2e: {
    responseTimeout: 20000,
    chromeWebSecurity: false,
    setupNodeEvents(on, config) {
         on('task', {downloadFile})
    },
  }
});
