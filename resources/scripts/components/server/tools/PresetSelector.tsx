import React from 'react';
import { useTranslation } from 'react-i18next';

export type PackSquashPreset = 'balanced' | 'aggressive' | 'maximum' | 'custom';

interface Props {
    value: PackSquashPreset;
    onChange: (preset: PackSquashPreset) => void;
    disabled?: boolean;
}

const presets: { key: PackSquashPreset; label: string }[] = [
    { key: 'balanced', label: 'preset-balanced' },
    { key: 'aggressive', label: 'preset-aggressive' },
    { key: 'maximum', label: 'preset-maximum' },
    { key: 'custom', label: 'preset-custom' },
];

export default function PresetSelector({ value, onChange, disabled }: Props) {
    const { t } = useTranslation('arix/server/tools');

    return (
        <div className={'space-y-2'}>
            <label className={'text-sm font-medium text-gray-300'}>{t('preset')}</label>
            <div className={'grid grid-cols-2 sm:grid-cols-4 gap-2'}>
                {presets.map((preset) => (
                    <label
                        key={preset.key}
                        className={`
                            flex items-center justify-center px-4 py-3 rounded-box cursor-pointer
                            transition-colors border
                            ${value === preset.key
                                ? 'bg-blue-600 border-blue-500 text-white'
                                : 'bg-gray-700 border-gray-600 text-gray-300 hover:bg-gray-600'
                            }
                            ${disabled ? 'opacity-50 cursor-not-allowed' : ''}
                        `}
                    >
                        <input
                            type={'radio'}
                            name={'preset'}
                            value={preset.key}
                            checked={value === preset.key}
                            onChange={() => onChange(preset.key)}
                            disabled={disabled}
                            className={'sr-only'}
                        />
                        <span className={'text-sm font-medium'}>{t(preset.label)}</span>
                    </label>
                ))}
            </div>
        </div>
    );
}