import React from 'react';
import { useTranslation } from 'react-i18next';

interface Props {
    status: 'idle' | 'running' | 'complete' | 'error';
    message?: string;
}

export default function OptimizationProgress({ status, message }: Props) {
    const { t } = useTranslation('arix/server/tools');

    if (status === 'idle') return null;

    const getMessage = () => {
        if (message) return message;
        switch (status) {
            case 'running':
                return t('progress.processing');
            case 'complete':
                return t('progress.complete');
            default:
                return '';
        }
    };

    return (
        <div className={'bg-gray-700 rounded-box p-4 mt-4'}>
            <div className={'flex items-center gap-3'}>
                {status === 'running' && (
                    <div className={'w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin'} />
                )}
                {status === 'complete' && (
                    <div className={'w-5 h-5 rounded-full bg-green-500 flex items-center justify-center'}>
                        <span className={'text-white text-xs'}>✓</span>
                    </div>
                )}
                {status === 'error' && (
                    <div className={'w-5 h-5 rounded-full bg-red-500 flex items-center justify-center'}>
                        <span className={'text-white text-xs'}>!</span>
                    </div>
                )}
                <span className={'text-sm text-gray-300'}>{getMessage()}</span>
            </div>
        </div>
    );
}