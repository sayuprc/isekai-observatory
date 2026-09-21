import { treaty } from '@elysiajs/eden';
import { PUBLIC_APP_URL } from 'astro:env/client';
import type { App } from '../server';

const getCookie = (name: string): string | undefined => {
  // SSR 時は取れないので undefined にする
  if (typeof document === 'undefined') {
    return undefined;
  }

  const value = document.cookie
    .split('; ')
    .find(row => row.startsWith(`${name}=`))
    ?.split('=')[1];

  return value ? decodeURIComponent(value) : undefined;
};

const getCsrfHeaders = (): Record<string, string> => {
  const csrfToken = getCookie('csrf');

  return csrfToken ? { 'x-csrf-token': csrfToken } : {};
};

export const client = treaty<App>(PUBLIC_APP_URL, {
  headers: getCsrfHeaders,
});
