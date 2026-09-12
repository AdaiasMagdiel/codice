import { defineConfig } from 'vitepress'

export default defineConfig({
  lang: 'it',
  title: 'Codice',
  description: 'Un piccolo linguaggio di programmazione con parole chiave in italiano.',

  head: [
    ['link', { rel: 'icon', href: '/favicon.svg', type: 'image/svg+xml' }],
  ],

  themeConfig: {
    logo: '/logo.svg',
    siteTitle: 'Codice',

    nav: [
      { text: 'Guida', link: '/guida/introduzione', activeMatch: '/guida/' },
      { text: 'Esempi', link: '/esempi', activeMatch: '/esempi' },
      { text: 'REPL', link: '/repl', activeMatch: '/repl' },
    ],

    sidebar: {
      '/guida/': [
        {
          text: 'Introduzione',
          items: [
            { text: "Cos'è Codice?", link: '/guida/introduzione' },
            { text: 'Installazione', link: '/guida/installazione' },
            { text: 'Il REPL', link: '/guida/repl' },
          ],
        },
        {
          text: 'Il Linguaggio',
          items: [
            { text: 'Tipi primitivi', link: '/guida/tipi' },
            { text: 'Variabili', link: '/guida/variabili' },
            { text: 'Operazioni matematiche', link: '/guida/operazioni' },
            { text: 'Stringhe', link: '/guida/stringhe' },
            { text: 'Funzioni built-in', link: '/guida/builtin' },
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
      message: 'Rilasciato sotto licenza GPL-3.0.',
      copyright: 'Copyright © 2024 Adaías Magdiel',
    },

    editLink: {
      pattern: 'https://github.com/AdaiasMagdiel/codice/edit/main/docs/:path',
      text: 'Modifica questa pagina su GitHub',
    },

    lastUpdated: {
      text: 'Aggiornato il',
    },

    docFooter: {
      prev: 'Precedente',
      next: 'Successivo',
    },

    outline: {
      label: 'In questa pagina',
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
