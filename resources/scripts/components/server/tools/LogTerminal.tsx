import React, { useEffect, useRef } from 'react';

interface LogTerminalProps {
    logs: string[];
    isLive?: boolean;
    title?: string;
}

export default function LogTerminal({ logs, isLive = false, title = 'PackSquash Output' }: LogTerminalProps) {
    const containerRef = useRef<HTMLDivElement>(null);

    // Auto-scroll to bottom when new logs arrive
    useEffect(() => {
        if (containerRef.current) {
            containerRef.current.scrollTop = containerRef.current.scrollHeight;
        }
    }, [logs]);

    const copyToClipboard = async () => {
        const text = logs.join('\n');
        await navigator.clipboard.writeText(text);
    };

    if (logs.length === 0) {
        return (
            <div className="bg-gray-900 border border-gray-700 rounded-xl overflow-hidden">
                <div className="flex items-center justify-between px-4 py-2.5 bg-gray-800 border-b border-gray-700">
                    <div className="flex items-center gap-2">
                        <div className="w-3 h-3 rounded-full bg-red-500/80" />
                        <div className="w-3 h-3 rounded-full bg-yellow-500/80" />
                        <div className="w-3 h-3 rounded-full bg-green-500/80" />
                    </div>
                    <span className="text-xs font-mono text-gray-500 tracking-[1px]">{title}</span>
                </div>
                <div className="p-8 flex items-center justify-center text-gray-500 text-sm font-mono">
                    Waiting for output...
                </div>
            </div>
        );
    }

    return (
        <div className="bg-gray-900 border border-gray-700 rounded-xl overflow-hidden shadow-2xl">
            {/* Terminal header */}
            <div className="flex items-center justify-between px-4 py-2.5 bg-gray-800 border-b border-gray-700">
                <div className="flex items-center gap-2">
                    <div className="w-3 h-3 rounded-full bg-red-500/90" />
                    <div className="w-3 h-3 rounded-full bg-yellow-500/90" />
                    <div className="w-3 h-3 rounded-full bg-green-500/90" />
                </div>
                <div className="flex items-center gap-3">
                    <span className="text-xs font-mono text-gray-400 tracking-[1.5px]">{title}</span>
                    {isLive && (
                        <div className="flex items-center gap-1.5">
                            <div className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
                            <span className="text-[10px] font-mono text-emerald-400 tracking-widest">LIVE</span>
                        </div>
                    )}
                    <button
                        onClick={copyToClipboard}
                        className="text-[10px] px-2 py-0.5 rounded bg-gray-700 hover:bg-gray-600 text-gray-400 hover:text-gray-200 transition-colors"
                    >
                        Copy
                    </button>
                </div>
            </div>

            {/* Terminal body */}
            <div
                ref={containerRef}
                role="log"
                aria-live="polite"
                className="p-4 max-h-[320px] overflow-y-auto font-mono text-sm leading-relaxed text-gray-100 bg-gray-900 scrollbar-thin scrollbar-thumb-gray-700 scrollbar-track-transparent"
            >
                {logs.map((line, index) => (
                    <div
                        key={index}
                        className={`whitespace-pre-wrap break-all py-px ${
                            line.toLowerCase().includes('error') || line.toLowerCase().includes('fail')
                                ? 'text-red-400'
                                : line.toLowerCase().includes('warn')
                                ? 'text-yellow-400'
                                : 'text-gray-100'
                        }`}
                    >
                        <span className="text-gray-600 select-none mr-3 tabular-nums">{(index + 1).toString().padStart(3, ' ')}</span>
                        {line}
                    </div>
                ))}
            </div>

            {/* Footer */}
            <div className="px-4 py-1.5 bg-gray-800 border-t border-gray-700 text-[10px] text-gray-500 font-mono tracking-[1px]">
                {logs.length} lines • {isLive ? 'Streaming' : 'Complete'}
            </div>
        </div>
    );
}