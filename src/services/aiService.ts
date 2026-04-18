
import { generateProjectPitch as generateWithGemini } from './geminiService';

export type AIProvider = 'gemini' | 'deepseek';

export async function generateContent(url: string, title: string, provider: AIProvider = 'gemini', customKey?: string) {
  if (provider === 'gemini') {
    return generateWithGemini(url, title, customKey);
  }
  
  // All other AI requests go through our proxy to support API Manager overrides
  const response = await fetch('/api/ai/generate', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ url, title, provider })
  });
  
  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || error.error || `${provider} generation failed`);
  }
  
  return response.json();
}
