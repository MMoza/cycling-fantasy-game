import { SVGAttributes } from 'react';

export default function ApplicationLogo(props: SVGAttributes<SVGElement>) {
    return (
        <svg
            {...props}
            viewBox="0 0 40 40"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <circle cx="20" cy="20" r="18" className="fill-brand-600 dark:fill-accent-500" />
            <path
                d="M12 22c0-4.418 3.582-8 8-8s8 3.582 8 8"
                className="stroke-white dark:stroke-brand-900"
                strokeWidth="2.5"
                strokeLinecap="round"
            />
            <circle cx="20" cy="22" r="4" className="fill-white dark:fill-brand-900" />
            <circle cx="20" cy="22" r="2" className="fill-brand-600 dark:fill-accent-500" />
            <path
                d="M10 26h20"
                className="stroke-white dark:stroke-brand-900"
                strokeWidth="2"
                strokeLinecap="round"
            />
            <path
                d="M14 30c2-1 4-1.5 6-1.5s4 .5 6 1.5"
                className="stroke-white dark:stroke-brand-900"
                strokeWidth="2"
                strokeLinecap="round"
            />
        </svg>
    );
}
