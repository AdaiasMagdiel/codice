import { defineConfig } from 'vitepress'

export default defineConfig({
  lang: 'en-US',
  title: 'Codice',
  description: 'A small programming language with Italian keywords.',

  head: [
    ['link', { rel: 'icon', href: '/favicon.svg', type: 'image/svg+xml' }],
  ],

  themeConfig: {
    logo: '/logo.svg',
    siteTitle: 'Codice',

    nav: [
      { text: 'Guide', link: '/guide/introduction', activeMatch: '/guide/' },
      { text: 'Examples', link: '/examples', activeMatch: '/examples' },
      { text: 'REPL', link: '/repl', activeMatch: '/repl' },
    ],

    sidebar: {
      '/guide/': [
        {
          text: 'Introduction',
          items: [
            { text: 'What is Codice?', link: '/guide/introduction' },
            { text: 'Installation', link: '/guide/installation' },
            { text: 'The REPL', link: '/guide/repl' },
          ],
        },
        {
          text: 'The Language',
          items: [
            { text: 'Primitive types', link: '/guide/types' },
            { text: 'Variables', link: '/guide/variables' },
            { text: 'Math operations', link: '/guide/operations' },
            { text: 'Strings', link: '/guide/strings' },
            { text: 'Built-in functions', link: '/guide/builtin' },
          ],
        },
      ],
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/AdaiasMagdiel/codice' },
    ],

    search: {
      provider: 'local',
    },

    footer: {
      message: 'Released under the GPL-3.0 license.',
      copyright: 'Copyright © 2024 Adaías Magdiel',
    },

    editLink: {
      pattern: 'https://github.com/AdaiasMagdiel/codice/edit/main/docs/:path',
      text: 'Edit this page on GitHub',
    },

    lastUpdated: {
      text: 'Last updated',
    },
  },

  markdown: {
    languages: [
      {
        name: 'codice',
        aliases: ['cod'],
        scopeName: 'source.cod',
        grammar: {
          patterns: [
            { match: '\\b(sia|stampa|vero|falso|nullo|esci)\\b', name: 'keyword.control.cod' },
            { match: '\\b(stringa|intero|decimale|booleano)\\b', name: 'storage.type.cod' },
            { match: '"[^"]*"', name: 'string.quoted.double.cod' },
            { match: '\\b\\d+(\\.\\d+)?\\b', name: 'constant.numeric.cod' },
            { match: '(→[^\n]*)', name: 'comment.line.cod' },
          ],
        },
      },
    ],
  },
})
