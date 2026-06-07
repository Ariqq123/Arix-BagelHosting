import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import PresetSelector, { PackSquashPreset } from './PresetSelector';
import OptimizationProgress from './OptimizationProgress';
import ResultActions from './ResultActions';
import { Button } from '@/components/elements/button/index';

interface ResultData {
    downloadUrl?: string;
    viewUrl?: string;
    originalSize?: number;
    optimizedSize?: number;
}

export default function PackSquashTool() {
    const { t } = useTranslation('arix/server/tools');
    const [preset, setPreset] = useState<PackSquashPreset>('balanced');
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [status, setStatus] = useState<'idle' | 'running' | 'complete' | 'error'>('idle');
    const [result, setResult] = useState<ResultData | null>(null);
    const [error, setError] = useState<string | null>(null);

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            if (!file.name.endsWith('.zip')) {
                setError(t('errors.invalid-file'));
                return;
            }
            setSelectedFile(file);
            setError(null);
        }
    };

    const handleRun = async () => {
        if (!selectedFile) {
            setError(t('errors.no-file'));
            return;
        }

        setStatus('running');
        setError(null);
        setResult(null);

        // Placeholder for actual API call
        // In production, this would POST to /api/client/servers/:uuid/tools/packsquash
        try {
            // Simulate processing
            await new Promise(resolve => setTimeout(resolve, 2000));

            // Mock result for UI demonstration
            setResult({
                downloadUrl: '#',
                viewUrl: '#',
                originalSize: selectedFile.size,
                optimizedSize: Math.floor(selectedFile.size * 0.6),
            });
            setStatus('complete');
        } catch (err) {
            setStatus('error');
            setError(t('errors.optimization-failed'));
        }
    };

    return (
        <div className={'bg-gray-700 rounded-box p-6'}>
            <div className={'mb-6'}>
                <h3 className={'text-xl font-semibold text-gray-100 mb-1'}>{t('packsquash')}</h3>
                <p className={'text-sm text-gray-400'}>{t('packsquash-description')}</p>
            </div>

            <div className={'space-y-6'}>
                <PresetSelector value={preset} onChange={setPreset} disabled={status === 'running'} />

                <div className={'space-y-2'}>
                    <label className={'text-sm font-medium text-gray-300'}>{t('input-file')}</label>
                    <div className={'relative'}>
                        <input
                            type={'file'}
                            accept={'.zip'}
                            onChange={handleFileChange}
                            disabled={status === 'running'}
                            className={'block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-component file:border-0 file:text-sm file:font-medium file:bg-gray-600 file:text-gray-100 hover:file:bg-gray-500 disabled:opacity-50'}
                        />
                    </div>
                    {selectedFile && (
                        <p className={'text-xs text-gray-400 mt-1'}>{selectedFile.name}</p>
                    )}
                </div>

                <div>
                    <Button onClick={handleRun} disabled={status === 'running' || !selectedFile}>
                        {status === 'running' ? t('running') : t('run-optimization')}
                    </Button>
                </div>

                {error && (
                    <div className={'bg-red-900 bg-opacity-30 border border-red-700 rounded-component px-4 py-3 text-sm text-red-300'}>
                        {error}
                    </div>
                )}

                <OptimizationProgress status={status} />

                {result && <ResultActions result={result} />}
            </div>
        </div>
    );
}