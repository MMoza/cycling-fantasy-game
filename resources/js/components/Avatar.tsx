import { cn } from '@/lib/utils';

interface AvatarProps {
    user: { name: string; avatar?: string | null };
    size?: 'sm' | 'md' | 'lg';
    className?: string;
    isOnline?: boolean;
}

const sizeClasses = {
    sm: 'h-8 w-8 text-xs',
    md: 'h-10 w-10 text-sm',
    lg: 'h-20 w-20 text-lg',
};

const dotPositionClasses = {
    sm: 'bottom-0 right-0',
    md: 'bottom-0 right-0',
    lg: 'bottom-[6px] right-[6px]',
};

function getInitials(name: string): string {
    return name
        .split(' ')
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();
}

export default function Avatar({ user, size = 'md', className, isOnline }: AvatarProps) {
    const initials = !user.avatar && (
        <div
            className={cn(
                'flex items-center justify-center rounded-full bg-brand-600 font-medium text-white',
                sizeClasses[size],
                className,
            )}
        >
            {getInitials(user.name)}
        </div>
    );

    return (
        <div className="relative inline-flex shrink-0">
            {user.avatar ? (
                <img
                    src={user.avatar}
                    alt={user.name}
                    className={cn('rounded-full object-cover', sizeClasses[size], className)}
                />
            ) : (
                initials
            )}
            {isOnline && (
                <span
                    className={cn(
                        'absolute block h-2.5 w-2.5 rounded-full bg-green-500 ring-2 ring-background',
                        dotPositionClasses[size],
                    )}
                />
            )}
        </div>
    );
}
