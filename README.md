# Leadership Academy — React

React/Vite conversion of the supplied Leadership Academy website. The visual styling, sections, mobile navigation, contact form, authentication, account page, assets, and existing PHP API endpoints are preserved.

## Run

```bash
npm install
npm run dev
```

For production:
```bash
npm run build
npm run preview
```

The PHP files in `public/Api` are retained and should be served by PHP/MySQL in the same deployment environment. The React frontend calls them at `/Api/...`.
