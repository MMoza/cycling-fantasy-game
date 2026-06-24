import * as Flags from 'country-flag-icons/react/3x2';

const flagCache = Flags as Record<string, React.ComponentType<{ className?: string }>>;

export function FlagIcon({ code, className }: { code: string; className?: string }) {
  const Flag = flagCache[code.toUpperCase()];
  if (!Flag) return null;
  return <Flag className={className} />;
}
