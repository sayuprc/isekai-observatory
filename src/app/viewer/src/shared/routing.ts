export function isActivePath(currentPath: string, href: string): boolean {
  if (href === '/') return currentPath === '/';
  return currentPath === href || currentPath.startsWith(`${href}/`);
}
