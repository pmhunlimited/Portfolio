
import express from 'express';
import { createServer as createViteServer } from 'vite';
import path from 'path';
import { fileURLToPath } from 'url';
import admin from 'firebase-admin';
import firebaseConfig from './firebase-applet-config.json' with { type: 'json' };

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

async function startServer() {
  const app = express();
  const PORT = 3000;

  // Initialize Firebase Admin
  // Note: In this environment, we can often rely on default credentials if they exist,
  // but here we use the project ID from our config.
  if (!admin.apps.length) {
    admin.initializeApp({
      projectId: firebaseConfig.projectId,
    });
  }
  const db = admin.firestore();

  const getApiKey = async (envName: string, configName: string) => {
    // Priority: Environment Variable > Firestore
    if (process.env[envName]) return process.env[envName];
    
    try {
      const keysDoc = await db.collection('settings').doc('keys').get();
      if (keysDoc.exists) {
        return keysDoc.data()?.[configName] || null;
      }
    } catch (e) {
      console.error(`Error fetching ${configName} from vault:`, e);
    }
    return null;
  };

  app.use(express.json());

  // AI Content Generation Proxy
  app.post('/api/ai/generate', async (req, res) => {
    const { url, title, provider } = req.body;
    
    if (provider === 'deepseek') {
      const apiKey = await getApiKey('DEEPSEEK_API_KEY', 'deepseek');
      if (!apiKey) {
        return res.status(500).json({ status: 'error', message: 'DEEPSEEK_API_KEY not configured' });
      }

      try {
        const prompt = `
          Analyze the provided URL: ${url}
          Title: ${title}
          
          Generate a high-end portfolio project entry in JSON format with:
          - content: Markdown (Problem -> Solution -> Result) in 2-3 paragraphs.
          - metaTitle: SEO Title (max 60 chars)
          - metaDescription: SEO Description (max 160 chars)
          - keywords: 5-8 relevant tags
          - techStack: Array of { name: string } based on the site.
          - waMessage: Professional WhatsApp inquiry message.
          
          Respond ONLY with a valid JSON object.
        `;

        const response = await fetch('https://api.deepseek.com/chat/completions', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${apiKey}`
          },
          body: JSON.stringify({
            model: 'deepseek-chat',
            messages: [{ role: 'user', content: prompt }],
            response_format: { type: 'json_object' }
          })
        });

        const data = await response.json();
        const content = data.choices[0].message.content;
        res.json(JSON.parse(content));
      } catch (error) {
        console.error('DeepSeek Error:', error);
        res.status(500).json({ status: 'error', message: 'DeepSeek generation failed' });
      }
    } else {
      res.status(400).json({ status: 'error', message: 'Invalid provider' });
    }
  });

  // Google PageSpeed Screenshot Proxy
  app.get('/api/pagespeed/screenshot', async (req, res) => {
    const { url } = req.query;
    const apiKey = await getApiKey('GOOGLE_PAGESPEED_API_KEY', 'pagespeed');

    if (!url || !apiKey) {
      return res.status(400).json({ status: 'error', message: 'Missing URL or API Key' });
    }

    try {
      const apiUrl = `https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=${encodeURIComponent(url as string)}&category=performance&key=${apiKey}`;
      const response = await fetch(apiUrl);
      const data = await response.json();
      
      const screenshot = data.lighthouseResult?.audits?.['final-screenshot']?.details?.data;
      
      if (screenshot) {
        res.json({ screenshot });
      } else {
        res.status(404).json({ status: 'error', message: 'Screenshot not found in report' });
      }
    } catch (error) {
      console.error('PageSpeed Error:', error);
      res.status(500).json({ status: 'error', message: 'Screenshot capture failed' });
    }
  });

  // Sitemap Dynamic Generation
  app.get('/sitemap.xml', async (req, res) => {
    try {
      const projectsSnapshot = await db.collection('projects').get();
      const projects = projectsSnapshot.docs.map(doc => doc.data());

      const sitemap = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>${process.env.APP_URL || 'http://localhost:3000'}/</loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>
  ${projects.map(p => `
  <url>
    <loc>${process.env.APP_URL || 'http://localhost:3000'}/project/${p.slug}</loc>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>`).join('')}
</urlset>`;

      res.header('Content-Type', 'application/xml');
      res.status(200).send(sitemap);
    } catch (error) {
      console.error('Sitemap generation error:', error);
      res.status(500).send('Error generating sitemap');
    }
  });

  // API Routes
  app.get('/api/health', (req, res) => {
    res.json({ status: 'ok' });
  });

  // Vite middleware
  if (process.env.NODE_ENV !== 'production') {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: 'spa',
    });
    app.use(vite.middlewares);
  } else {
    const distPath = path.join(process.cwd(), 'dist');
    app.use(express.static(distPath));
    app.get('*', (req, res) => {
      res.sendFile(path.join(distPath, 'index.html'));
    });
  }

  app.listen(PORT, '0.0.0.0', () => {
    console.log(`Server running on http://localhost:${PORT}`);
    console.log(`Sitemap available at http://localhost:${PORT}/sitemap.xml`);
  });
}

startServer();
