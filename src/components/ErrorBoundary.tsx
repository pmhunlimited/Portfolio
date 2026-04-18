
import React, { Component, ErrorInfo, ReactNode } from 'react';
import { AlertTriangle, Home as HomeIcon } from 'lucide-react';

interface Props {
  children?: ReactNode;
}

interface State {
  hasError: boolean;
  error: Error | null;
}

export default class ErrorBoundary extends Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = {
      hasError: false,
      error: null
    };
  }

  public static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error };
  }

  public componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    console.error('Uncaught error:', error, errorInfo);
  }

  public render() {
    if (this.state.hasError) {
      return (
        <div className="min-h-screen bg-black flex flex-col items-center justify-center p-6 text-center">
          <div className="bg-white/5 border border-white/10 p-12 rounded-[24px] max-w-lg space-y-6 glass-purple relative overflow-hidden">
            <div className="absolute -top-10 -left-10 w-40 h-40 bg-sharp-orange/10 blur-[80px] rounded-full"></div>
            
            <div className="flex justify-center">
              <div className="w-20 h-20 rounded-full bg-sharp-orange/20 flex items-center justify-center animate-pulse border border-sharp-orange/30">
                <AlertTriangle className="w-10 h-10 text-sharp-orange" />
              </div>
            </div>

            <div className="space-y-2">
              <h1 className="text-3xl font-black italic tracking-tighter uppercase text-glow-orange">
                System <span className="text-sharp-orange">Fracture</span>
              </h1>
              <p className="text-text-dim font-mono text-xs uppercase tracking-[0.2em]">
                Critical logic failure detected in core node.
              </p>
            </div>

            <div className="bg-black/40 border border-white/5 p-4 rounded-lg text-left overflow-auto max-h-40">
              <code className="text-sharp-orange font-mono text-[10px] break-all">
                {this.state.error?.toString()}
              </code>
            </div>

            <button
              onClick={() => window.location.href = '/'}
              className="w-full py-4 bg-sharp-orange text-black font-black rounded-xl flex items-center justify-center gap-3 neon-orange hover:brightness-110 active:scale-95 transition-all text-sm uppercase tracking-widest"
            >
              <HomeIcon className="w-5 h-5" />
              Return to Safe Grid
            </button>
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}
