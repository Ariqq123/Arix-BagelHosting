import React from 'react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/elements/button/index';
import { DownloadIcon, ExternalLinkIcon } from '@heroicons/react/outline';

interface ResultData {
    downloadUrl?: string;
    viewUrl?: string;
    originalSize?: number;
    optimizedSize?: number;
}

interface Props {
    result: ResultData;
}

function formatBytes(bytes: number): string {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

export default function ResultActions({ result }: Props) {
    const { t } = useTranslation('arix/server/tools');

    const savings = result.originalSize && result.optimizedSize
        ? Math.round((1 - result.optimizedSize / result.originalSize) * 100)
        : null;

    return (
        <div className={'bg-gray-700 rounded-box p-5 mt-4'}>
            <h4 className={'text-sm font-medium text-gray-300 mb-3'}>{t('results.download')}</h4>

            <div className={'flex flex-wrap gap-2 mb-4'}>
                {result.downloadUrl && (
                    <a href={result.downloadUrl} download className={'inline-block'}>
                        <Button>
                            <DownloadIcon className={'w-4 mr-2'} />
                            {t('results.download')}
                        </Button>
                    </a>
                )}
                {result.viewUrl && (
                    <a href={result.viewUrl} target={'_blank'} rel={'noreferrer'} className={'inline-block'}>
                        <Button.Text>
                            <ExternalLinkIcon className={'w-4 mr-2'} />
                            {t('results.view-online')}
                        </Button.Text>
                    </a>
                )}
            </div>

            {(result.originalSize || result.optimizedSize) && (
                <div className={'flex flex-wrap gap-4 text-sm text-gray-300 pt-3 border-t border-gray-600'}>
                    {result.originalSize && (
                        <span>{t('results.original-size')}: {formatBytes(result.originalSize)}</span>
                    )}
                    {result.optimizedSize && (
                        <span>{t('results.optimized-size')}: {formatBytes(result.optimizedSize)}</span>
                    )}
                    {savings !== null && (
                        <span className={'text-green-400'}>
                            {t('results.savings')}: {savings}%
                        </span>
                    )}
                </div>
            )}
        </div>
    );
}