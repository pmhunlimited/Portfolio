
import { GoogleGenAI, Type } from "@google/genai";

export async function generateProjectPitch(url: string, title?: string, customKey?: string) {
  const apiKey = customKey || process.env.GEMINI_API_KEY;
  if (!apiKey) throw new Error("GEMINI_API_KEY is not configured.");

  const ai = new GoogleGenAI({ apiKey });
  const model = "gemini-3-flash-preview";
    const prompt = `
    Perform a deep-scan and analysis of the provided URL to extract its core value proposition, technical architecture, and visual identity.
    Your goal is to generate a "Power Pitch" that is 100% inline with exactly what the website offers and stands for. Do not hallucinate features that aren't present.
    
    Target URL: ${url}
    Title Context: ${title || 'Determine from content'}
    
    The response must be a JSON object containing:
    1. content: A "Power Pitch" following the Problem -> Solution -> Result framework, reflecting the REAL content of the site. 
       - CRITICAL: Provide the content as well-paragraphed Markdown with 2-3 clear paragraphs for maximum readability.
    2. metaTitle: A high-performing SEO title (max 60 chars).
    3. metaDescription: A compelling SEO description (max 160 chars) accurately reflecting the site's purpose.
    4. keywords: Array of 5-8 highly relevant keywords.
    5. techStack: Array of objects with name (e.g. React, Node.js) based on visible tech or likely stack.
    6. waMessage: A professional WhatsApp inquiry message tailored to this specific service.
  `;

  const response = await ai.models.generateContent({
    model,
    contents: prompt,
    config: {
      responseMimeType: "application/json",
      responseSchema: {
        type: Type.OBJECT,
        properties: {
          content: { type: Type.STRING },
          metaTitle: { type: Type.STRING },
          metaDescription: { type: Type.STRING },
          keywords: { type: Type.ARRAY, items: { type: Type.STRING } },
          techStack: {
            type: Type.ARRAY,
            items: {
              type: Type.OBJECT,
              properties: {
                name: { type: Type.STRING },
                icon: { type: Type.STRING }
              }
            }
          },
          waMessage: { type: Type.STRING }
        },
        required: ["content", "metaTitle", "metaDescription", "keywords", "techStack", "waMessage"]
      },
      tools: [{ googleSearch: {} }],
      toolConfig: { includeServerSideToolInvocations: true }
    }
  });

  return JSON.parse(response.text);
}
