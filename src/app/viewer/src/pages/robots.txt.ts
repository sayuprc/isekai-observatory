import type { APIRoute } from 'astro';

const noindex = import.meta.env.SITE_NOINDEX === 'true';

export const GET: APIRoute = ({ site }) => {
  const body = noindex
    ? ['User-agent: *', 'Disallow: /', ''].join('\n')
    : ['User-agent: *', 'Disallow:', '', `Sitemap: ${new URL('sitemap-index.xml', site)}`, ''].join('\n');

  return new Response(body, {
    headers: { 'Content-Type': 'text/plain; charset=utf-8' },
  });
};
