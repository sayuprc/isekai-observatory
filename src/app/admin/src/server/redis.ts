import { Redis } from '@upstash/redis';
import { CACHE_TOKEN, CACHE_URL } from 'astro:env/server';

export const redis = new Redis({
  url: CACHE_URL,
  token: CACHE_TOKEN,
});
