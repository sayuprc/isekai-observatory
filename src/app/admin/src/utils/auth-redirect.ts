export const DEFAULT_AUTH_RETURN_TO = '/song-types';
const PUBLIC_ROUTE_PREFIXES = ['/api/', '/auth/', '/_astro/'] as const satisfies readonly string[];
const PUBLIC_ROUTE_PATHS = ['/api', '/auth', '/favicon.svg'] as const satisfies readonly string[];
const PUBLIC_ROUTE_PATH_SET: ReadonlySet<string> = new Set(PUBLIC_ROUTE_PATHS);

export const resolveAuthReturnTo = (returnTo: string | null | undefined): string => {
  const path = returnTo?.trim();

  if (!path || !path.startsWith('/') || path.startsWith('//')) {
    return DEFAULT_AUTH_RETURN_TO;
  }

  return path;
};

export const isAuthRequiredPath = (pathname: string): boolean => {
  if (PUBLIC_ROUTE_PATH_SET.has(pathname)) {
    return false;
  }

  if (PUBLIC_ROUTE_PREFIXES.some(prefix => pathname.startsWith(prefix))) {
    return false;
  }

  return true;
};

export const createLoginRedirectPath = (returnTo: string): string => {
  const searchParams = new URLSearchParams({
    return_to: returnTo,
  });

  return `/auth/login?${searchParams.toString()}`;
};
