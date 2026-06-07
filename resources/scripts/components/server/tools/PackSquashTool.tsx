import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import PresetSelector, { PackSquashPreset } from './PresetSelector';
import OptimizationProgress from './OptimizationProgress';
import ResultActions from './ResultActions';
import LogTerminal from './LogTerminal';
import { Button } from '@/components/elements/button/index';
import { ServerContext } from '@/state/server';
import axios from 'axios';
import ServerContentBlock from '@/components/elements/ServerContentBlock';

interface ResultData {
    downloadUrl?: string;
    viewUrl?: string;
    originalSize?: number;
    optimizedSize?: number;
}

export default function PackSquashTool() {
    const { t } = useTranslation('arix/server/tools');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const [preset, setPreset] = useState<PackSquashPreset>('balanced');
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [status, setStatus] = useState<'idle' | 'uploading' | 'running' | 'complete' | 'error'>('idle');
    const [uploadProgress, setUploadProgress] = useState(0);
    const [result, setResult] = useState<ResultData | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [logs, setLogs] = useState<string[]>([]);

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

        setStatus('uploading');
        setUploadProgress(0);
        setError(null);
        setResult(null);
        setLogs([]);

        try {
            const formData = new FormData();
            formData.append('pack', selectedFile);
            formData.append('preset', preset);

            const response = await axios.post(
                `/api/client/servers/${uuid}/tools/packsquash`,
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                    onUploadProgress: (progressEvent) => {
                        const percentCompleted = Math.round(
                            (progressEvent.loaded * 100) / (progressEvent.total || 1)
                        );
                        setUploadProgress(percentCompleted);
                    },
                }
            );

            const responseLogs = response.data.meta?.logs;
            if (responseLogs) {
                setLogs(responseLogs.split('\n').filter(Boolean));
            }

            setResult({
                downloadUrl: response.data.download_url,
                viewUrl: response.data.view_url,
                originalSize: response.data.original_size,
                optimizedSize: response.data.optimized_size,
            });
            setStatus('complete');
        } catch (err: any) {
            const responseLogs = err.response?.data?.run?.meta?.logs;
            if (responseLogs) {
                setLogs(responseLogs.split('\n').filter(Boolean));
            }

            setStatus('error');
            setError(err.response?.data?.error || t('errors.optimization-failed'));
        }
    };

    return (
        <ServerContentBlock title={t('packsquash')} description={t('packsquash-description')}>
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
                    <Button onClick={handleRun} disabled={status === 'uploading' || status === 'running' || !selectedFile}>
                        {status === 'uploading' ? `Uploading... ${uploadProgress}%` : status === 'running' ? t('running') : t('run-optimization')}
                    </Button>
                </div>

                {status === 'uploading' && (
                    <div className="w-full bg-gray-700 rounded-full h-2.5">
                        <div
                            className="bg-emerald-500 h-2.5 rounded-full transition-all duration-200"
                            style={{ width: `${uploadProgress}%` }}
                        />
                    </div>
                )}

                {error && (
                    <div className={'bg-red-900 bg-opacity-30 border border-red-700 rounded-component px-4 py-3 text-sm text-red-300'}>
                        {error}
                    </div>
                )}

                <OptimizationProgress status={status} />

                {(status === 'running' || logs.length > 0) && (
                    <LogTerminal
                        logs={logs}
                        isLive={status === 'running'}
                        title="PackSquash Output"
                    />
                )}

                {result && <ResultActions result={result} />}
            </div>
        </ServerContentBlock>
    );
}