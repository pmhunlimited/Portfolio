
import { useState, useEffect } from 'react';
import { collection, addDoc, serverTimestamp, doc, getDoc, setDoc, query, orderBy, onSnapshot, updateDoc, deleteDoc } from 'firebase/firestore';
import { db, auth, signInWithGoogle } from '../lib/firebase';
import { onAuthStateChanged, signOut, User } from 'firebase/auth';
import { generateContent, AIProvider } from '../services/aiService';
import { Project } from '../types';
import { Save, Sparkles, Loader2, Link as LinkIcon, Globe, Smartphone, Settings, Image as ImageIcon, ShieldCheck, Key, User as UserIcon, Pin, Trash2, Pencil, Plus, Lock, Unlock, Zap, Video } from 'lucide-react';
import MediaRenderer from '../components/MediaRenderer';

export default function Admin() {
  const [editingId, setEditingId] = useState<string | null>(null);
  const [isAuthorized, setIsAuthorized] = useState(false);
  const [adminUser, setAdminUser] = useState<User | null>(null);
  const [adminUserAttempt, setAdminUserAttempt] = useState('');
  const [adminPassAttempt, setAdminPassAttempt] = useState('');
  const [url, setUrl] = useState('');
  const [title, setTitle] = useState('');
  const [type, setType] = useState<'web' | 'app'>('web');
  const [thumbnailUrl, setThumbnailUrl] = useState('');
  const [galleryImages, setGalleryImages] = useState<string[]>(['', '', '', '', '']);
  const [demoLogin, setDemoLogin] = useState({ username: '', password: '', note: '' });
  const [accessPoints, setAccessPoints] = useState({
    superAdmin: { url: '', username: '', password: '', directLoginUrl: '' },
    admin: { url: '', username: '', password: '', directLoginUrl: '' },
    user: { url: '', username: '', password: '', directLoginUrl: '' }
  });
  const [isPinned, setIsPinned] = useState(false);
  const [loading, setLoading] = useState(false);
  const [generatedData, setGeneratedData] = useState<Partial<Project> | null>(null);
  const [uploadProgress, setUploadProgress] = useState<{[key: number]: number}>({});

  // Global Settings State
  const [appTitle, setAppTitle] = useState('CYBER-PULSE');
  const [heroSubtext, setHeroSubtext] = useState('Sophisticated full-stack engineering. Powered by Gemini AI. Crafted for the elite digital frontier.');
  const [defaultAI, setDefaultAI] = useState<AIProvider>('gemini');
  const [saveSettingsLoading, setSaveSettingsLoading] = useState(false);

  // Active generation provider
  const [activeAI, setActiveAI] = useState<AIProvider>('gemini');
  const [isFetchingScreenshot, setIsFetchingScreenshot] = useState(false);

  // Management State
  const [allProjects, setAllProjects] = useState<Project[]>([]);
  const [deleteId, setDeleteId] = useState<string | null>(null);
  const [isDeleting, setIsDeleting] = useState(false);
  const [activeTab, setActiveTab] = useState<'posting' | 'system' | 'apis'>('posting');

  // API Keys State
  const [apiKeys, setApiKeys] = useState({
    gemini: '',
    deepseek: '',
    pagespeed: '',
    adminUsername: 'philmorehost@gmail.com',
    adminPassword: 'password1234',
    authorizedEmail: 'philmorehost@gmail.com'
  });
  const [saveKeysLoading, setSaveKeysLoading] = useState(false);

  useEffect(() => {
    const unsubAuth = onAuthStateChanged(auth, (user) => {
      setAdminUser(user);
    });

    const fetchSettings = async () => {
      const docRef = doc(db, 'settings', 'global');
      const docSnap = await getDoc(docRef);
      if (docSnap.exists()) {
        const data = docSnap.data();
        setAppTitle(data.appTitle || 'CYBER-PULSE');
        setHeroSubtext(data.heroSubtext || '');
        if (data.defaultAI) {
          setDefaultAI(data.defaultAI);
          setActiveAI(data.defaultAI);
        }
      }
    };
    fetchSettings();

    const fetchApiKeys = async () => {
      const docRef = doc(db, 'settings', 'keys');
      const docSnap = await getDoc(docRef);
      if (docSnap.exists()) {
        const data = docSnap.data();
        setApiKeys({
          gemini: data.gemini || '',
          deepseek: data.deepseek || '',
          pagespeed: data.pagespeed || '',
          adminUsername: data.adminUsername || 'philmorehost@gmail.com',
          adminPassword: data.adminPassword || 'password1234',
          authorizedEmail: data.authorizedEmail || 'philmorehost@gmail.com'
        });
      }
    };
    fetchApiKeys();

    // Fetch projects for management
    const q = query(collection(db, 'projects'), orderBy('createdAt', 'desc'));
    const unsubscribe = onSnapshot(q, (snapshot) => {
      const projs = snapshot.docs.map(d => ({ id: d.id, ...d.data() } as Project));
      setAllProjects(projs);
    });

    return () => {
      unsubAuth();
      unsubscribe();
    };
  }, []);

  const handleGoogleLogin = async () => {
    try {
      await signInWithGoogle();
    } catch (error) {
      console.error(error);
      alert('Authentication Failed');
    }
  };

  const handleSaveSettings = async () => {
    setSaveSettingsLoading(true);
    try {
      await setDoc(doc(db, 'settings', 'global'), {
        appTitle,
        heroSubtext,
        defaultAI,
        updatedAt: serverTimestamp()
      });
      alert('Global settings updated!');
    } catch (error) {
      console.error(error);
      alert('Failed to update settings.');
    } finally {
      setSaveSettingsLoading(false);
    }
  };

  const handleSaveApiKeys = async () => {
    setSaveKeysLoading(true);
    try {
      await setDoc(doc(db, 'settings', 'keys'), {
        ...apiKeys,
        updatedAt: serverTimestamp()
      });
      alert('API keys updated in secure vault!');
    } catch (error) {
      console.error(error);
      alert('Failed to update API keys.');
    } finally {
      setSaveKeysLoading(false);
    }
  };

  const handleManualEntry = () => {
    setEditingId(null);
    setGeneratedData({
      title: title || 'New Interface Node',
      content: 'Manual entry protocol active. Describe the system architecture...',
      techStack: [
        { name: 'React' },
        { name: 'Node.js' },
        { name: 'Firebase' }
      ],
      url: url || 'https://',
      thumbnailUrl: thumbnailUrl || '',
      type: type,
      seoData: {
        metaTitle: title || 'New Project Codex',
        metaDescription: 'Custom meta description for the node...',
        keywords: ['portfolio', 'project', 'tech']
      },
      waMessage: ''
    });
  };

  const handleMagicGenerate = async () => {
    if (!url) return alert('Enter URL first');
    setLoading(true);
    setEditingId(null);
    try {
      const data = await generateContent(url, title, activeAI, activeAI === 'gemini' ? apiKeys.gemini : undefined);
      
      // PageSpeed automated screenshot
      let autoScreenshot = '';
      try {
        const screenshotRes = await fetch(`/api/pagespeed/screenshot?url=${encodeURIComponent(url)}`);
        const result = await screenshotRes.json();
        if (result.screenshot) {
          autoScreenshot = result.screenshot;
        }
      } catch (err) {
        console.warn('PageSpeed screenshot failed, falling back to microlink');
        autoScreenshot = `https://api.microlink.io/?url=${encodeURIComponent(url)}&screenshot=true&embed=screenshot.url&viewport.width=1920&viewport.height=1080&waitFor=3000`;
      }
      
      setGeneratedData({
        ...data,
        url,
        title: title || data.metaTitle.split('|')[0].trim(),
        type,
        thumbnailUrl: thumbnailUrl || autoScreenshot,
        seoData: {
          metaTitle: data.metaTitle,
          metaDescription: data.metaDescription,
          keywords: data.keywords
        }
      });

      if (!thumbnailUrl) setThumbnailUrl(autoScreenshot);

    } catch (error) {
      console.error(error);
      alert('Failed to generate pitch. Check console.');
    } finally {
      setLoading(false);
    }
  };

  const handleFetchScreenshot = async () => {
    if (!url) return alert('Enter site URL first');
    setIsFetchingScreenshot(true);
    try {
      const response = await fetch(`/api/pagespeed/screenshot?url=${encodeURIComponent(url)}`);
      const result = await response.json();
      if (result.screenshot) {
        setThumbnailUrl(result.screenshot);
      } else {
        alert(result.message || 'Screenshot failed');
      }
    } catch (error) {
      console.error(error);
      alert('Failed to connect to screenshot service');
    } finally {
      setIsFetchingScreenshot(false);
    }
  };

  const handleSave = async () => {
    if (!generatedData) return;
    setLoading(true);
    try {
      const slug = generatedData.title?.toLowerCase().replace(/ /g, '-').replace(/[^\w-]/g, '');
      const validGallery = galleryImages.filter(img => img.trim() !== '');
      
      const payload = {
        ...generatedData,
        thumbnailUrl, 
        galleryImages: validGallery,
        demoLogin: (demoLogin.username || demoLogin.password) ? demoLogin : null,
        accessPoints: {
          superAdmin: accessPoints.superAdmin.url || accessPoints.superAdmin.directLoginUrl ? accessPoints.superAdmin : null,
          admin: accessPoints.admin.url || accessPoints.admin.directLoginUrl ? accessPoints.admin : null,
          user: accessPoints.user.url || accessPoints.user.directLoginUrl ? accessPoints.user : null,
        },
        slug,
        isPinned,
        updatedAt: serverTimestamp()
      };

      if (editingId) {
        await updateDoc(doc(db, 'projects', editingId), payload);
        alert('Node configuration updated in the grid!');
      } else {
        await addDoc(collection(db, 'projects'), {
          ...payload,
          inquiriesCount: 0,
          performance: {
            speed: Math.floor(Math.random() * 20) + 80,
            security: Math.floor(Math.random() * 10) + 90
          },
          createdAt: serverTimestamp()
        });
        alert('Project saved successfully!');
      }

      // Reset
      setUrl('');
      setTitle('');
      setThumbnailUrl('');
      setGalleryImages(['', '', '', '', '']);
      setDemoLogin({ username: '', password: '', note: '' });
      setIsPinned(false);
      setEditingId(null);
      setGeneratedData(null);
    } catch (error) {
      console.error(error);
      alert('Failed to save project.');
    } finally {
      setLoading(false);
    }
  };

  const handleTogglePin = async (project: Project) => {
    try {
      if (!project.isPinned) {
        // Enforce max 4 pins if possible, but let admin manage it
        const pinnedCount = allProjects.filter(p => p.isPinned).length;
        if (pinnedCount >= 4) {
          if (!confirm('You already have 4 pinned projects. Pinning this will exceed the hero slots. Continue?')) return;
        }
      }
      await updateDoc(doc(db, 'projects', project.id), {
        isPinned: !project.isPinned
      });
    } catch (error) {
      console.error(error);
    }
  };

  const handleDeleteProject = async (projectId: string) => {
    setIsDeleting(true);
    try {
      await deleteDoc(doc(db, 'projects', projectId));
      setDeleteId(null);
    } catch (error: any) {
      console.error(error);
      if (error.code === 'permission-denied') {
        alert('PERMISSION_DENIED: Ensure you are authenticated as philmorehost@gmail.com');
      } else {
        alert('PURGE_FAILED: Check systems console.');
      }
    } finally {
      setIsDeleting(false);
    }
  };

  const handleEditClick = (project: Project) => {
    setEditingId(project.id);
    setUrl(project.url || '');
    setTitle(project.title);
    setType(project.type);
    setThumbnailUrl(project.thumbnailUrl);
    setGalleryImages(project.galleryImages && project.galleryImages.length > 0 
      ? [...project.galleryImages, ...Array(5 - project.galleryImages.length).fill('')].slice(0, 5) 
      : ['', '', '', '', '']
    );
    setDemoLogin({
      username: project.demoLogin?.username || '',
      password: project.demoLogin?.password || '',
      note: project.demoLogin?.note || ''
    });
    setAccessPoints({
      superAdmin: { 
        url: project.accessPoints?.superAdmin?.url || '', 
        username: project.accessPoints?.superAdmin?.username || '', 
        password: project.accessPoints?.superAdmin?.password || '', 
        directLoginUrl: project.accessPoints?.superAdmin?.directLoginUrl || '' 
      },
      admin: { 
        url: project.accessPoints?.admin?.url || '', 
        username: project.accessPoints?.admin?.username || '', 
        password: project.accessPoints?.admin?.password || '', 
        directLoginUrl: project.accessPoints?.admin?.directLoginUrl || '' 
      },
      user: { 
        url: project.accessPoints?.user?.url || '', 
        username: project.accessPoints?.user?.username || '', 
        password: project.accessPoints?.user?.password || '', 
        directLoginUrl: project.accessPoints?.user?.directLoginUrl || '' 
      }
    });
    setIsPinned(!!project.isPinned);
    setGeneratedData(project);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleReset = () => {
    setUrl('');
    setTitle('');
    setThumbnailUrl('');
    setGalleryImages(['', '', '', '', '']);
    setDemoLogin({ username: '', password: '', note: '' });
    setAccessPoints({
      superAdmin: { url: '', username: '', password: '', directLoginUrl: '' },
      admin: { url: '', username: '', password: '', directLoginUrl: '' },
      user: { url: '', username: '', password: '', directLoginUrl: '' }
    });
    setIsPinned(false);
    setEditingId(null);
    setGeneratedData(null);
    setUploadProgress({});
  };

  const handleFileDrop = async (e: React.DragEvent, index: number) => {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (file && (file.type.startsWith('image/') || file.type.startsWith('video/'))) {
      handleMediaFile(file, index);
    }
  };

  const handleMediaFile = (file: File, index: number) => {
    const reader = new FileReader();
    
    // Check if file is too large for DataURL (Firestore limit is ~1MB total doc size)
    if (file.size > 800000) {
      alert("FILE_OVERSIZE: Nodes are limited to ~800KB due to grid constraints. Please use an external URL for large assets.");
      return;
    }
    let progress = 0;
    const interval = setInterval(() => {
      progress += Math.random() * 30;
      if (progress >= 100) {
        progress = 100;
        clearInterval(interval);
      }
      setUploadProgress(prev => ({ ...prev, [index]: progress }));
    }, 200);

    reader.onload = (e) => {
      const result = e.target?.result as string;
      const newGallery = [...galleryImages];
      newGallery[index] = result;
      setGalleryImages(newGallery);
      setTimeout(() => {
        setUploadProgress(prev => {
          const next = { ...prev };
          delete next[index];
          return next;
        });
      }, 1000);
    };
    reader.readAsDataURL(file);
  };

  if (!isAuthorized) {
    const checkPass = async () => {
      // Default fallback credentials
      let masterUser = 'philmorehost@gmail.com';
      let masterKey = 'password1234';
      
      try {
        const docRef = doc(db, 'settings', 'keys');
        const docSnap = await getDoc(docRef);
        if (docSnap.exists()) {
          const data = docSnap.data();
          if (data.adminUsername) masterUser = data.adminUsername;
          if (data.adminPassword) masterKey = data.adminPassword;
        }
      } catch (e) {
        console.warn('Vault access restricted before auth, using system fallback.');
      }

      if (adminUserAttempt === masterUser && adminPassAttempt === masterKey) {
        setIsAuthorized(true);
      } else {
        alert('ACCESS_DENIED: Invalid Credentials');
      }
    };

    return (
      <div className="min-h-screen fixed inset-0 z-[1000] bg-pitch-black flex items-center justify-center p-6 overflow-hidden">
        {/* Animated Background Elements */}
        <div className="absolute top-1/4 left-1/4 w-[500px] h-[500px] bg-sharp-orange/5 blur-[120px] rounded-full animate-pulse"></div>
        <div className="absolute bottom-1/4 right-1/4 w-[400px] h-[400px] bg-glossy-purple/5 blur-[100px] rounded-full animate-pulse delay-700"></div>
        
        <div className="w-full max-w-lg bg-white/5 border border-white/10 p-12 rounded-[32px] space-y-10 backdrop-blur-2xl relative shadow-2xl">
          <div className="absolute -top-12 left-1/2 -translate-x-1/2">
            <div className="w-24 h-24 rounded-3xl bg-sharp-orange flex items-center justify-center rotate-12 shadow-[0_0_40px_rgba(255,102,0,0.4)]">
              <ShieldCheck className="w-12 h-12 text-black -rotate-12" />
            </div>
          </div>

          <div className="text-center pt-8 space-y-4">
            <h1 className="text-4xl font-black tracking-tighter uppercase italic text-glow-orange leading-tight">Elite Firewall<br/><span className="text-white">Active</span></h1>
            <p className="text-[11px] text-text-dim font-mono uppercase tracking-[0.3em] font-bold">Authentication Bypass Required</p>
          </div>
          
          <div className="space-y-6">
            <div className="space-y-4">
              <div className="group relative">
                <UserIcon className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-text-dim group-focus-within:text-sharp-orange transition-colors" />
                <input 
                  type="email"
                  value={adminUserAttempt}
                  onChange={(e) => setAdminUserAttempt(e.target.value)}
                  placeholder="USERNAME_IDENTITY"
                  className="w-full bg-black/40 border border-white/10 rounded-2xl py-5 pl-14 pr-4 outline-none focus:border-sharp-orange focus:bg-black/60 transition-all font-mono text-sm tracking-wide"
                />
              </div>

              <div className="group relative">
                <Key className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-text-dim group-focus-within:text-sharp-orange transition-colors" />
                <input 
                  type="password"
                  value={adminPassAttempt}
                  onChange={(e) => setAdminPassAttempt(e.target.value)}
                  onKeyDown={(e) => e.key === 'Enter' && checkPass()}
                  placeholder="MASTER_PASSPHRASE"
                  className="w-full bg-black/40 border border-white/10 rounded-2xl py-5 pl-14 pr-4 outline-none focus:border-sharp-orange focus:bg-black/60 transition-all font-mono text-sm tracking-wide"
                />
              </div>
            </div>

            <button 
              onClick={checkPass}
              className="group relative w-full py-5 bg-sharp-orange text-black font-black rounded-2xl uppercase tracking-[0.2em] italic text-sm overflow-hidden transition-all hover:scale-[1.02] active:scale-[0.98] shadow-lg"
            >
              <div className="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-500 skew-x-12"></div>
              <span className="relative flex items-center justify-center gap-3">
                <Zap className="w-4 h-4" />
                Initiate Handshake
              </span>
            </button>
          </div>
          
          <div className="pt-8 border-t border-white/5 text-center flex flex-col items-center gap-4">
            <div className="flex gap-2">
              {[0, 1, 2].map(i => (
                <div key={i} className="w-1.5 h-1.5 rounded-full bg-sharp-orange animate-bounce" style={{ animationDelay: `${i * 150}ms` }}></div>
              ))}
            </div>
            <p className="text-[10px] font-bold text-text-dim uppercase tracking-widest font-mono italic">Environment: Local Sec_Node_Active</p>
          </div>
        </div>
      </div>
    );
  }

  if (!adminUser) {
    return (
      <div className="min-h-[80vh] flex items-center justify-center p-6">
        <div className="w-full max-w-md bg-white/5 border border-white/10 p-10 rounded-[20px] space-y-8 backdrop-blur-xl transition-all">
          <div className="text-center space-y-4 font-mono">
            <ShieldCheck className="w-12 h-12 text-glossy-purple mx-auto animate-pulse" />
            <h1 className="text-2xl font-black text-white uppercase italic tracking-tighter">Authority Sync Pending</h1>
            <p className="text-[10px] text-text-dim uppercase tracking-[0.2em]">Secondary Authentication Layer Required to satisfatory Firestore rules</p>
          </div>
          <button 
            onClick={handleGoogleLogin}
            className="w-full py-4 bg-glossy-purple text-white font-black rounded-xl uppercase tracking-widest text-sm hover:brightness-110 transition-all flex items-center justify-center gap-2 shadow-[0_0_20px_rgba(168,85,247,0.3)]"
          >
            <Globe className="w-4 h-4" /> Sign In as Core Admin
          </button>
          <div className="text-[9px] text-center text-text-dim uppercase font-mono italic">
            Target Identification: philmorehost@gmail.com
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto py-12 px-4 md:px-0">
      <div className="flex flex-col md:flex-row items-center justify-between mb-8 gap-6">
        <h1 className="text-3xl md:text-5xl font-black tracking-tighter italic uppercase text-glow-orange text-center md:text-left">
          {editingId ? 'EDITING' : 'MAGIC'} <span className="text-sharp-orange">{editingId ? 'NODE' : 'POST'}</span> ENGINE
        </h1>
        <div className="flex items-center gap-4">
          <button 
            onClick={() => {
              signOut(auth);
              setIsAuthorized(false);
            }}
            className="p-2 rounded-lg bg-white/5 border border-white/10 text-text-dim hover:text-sharp-orange transition-all"
            title="Lock Console"
          >
            <Unlock className="w-4 h-4" />
          </button>
          {editingId && (
            <button 
              onClick={handleReset}
              className="flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 text-white rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-white/10 transition-all"
            >
              <Plus className="w-3 h-3" /> Start New Entry
            </button>
          )}
        </div>
      </div>

      <div className="flex gap-2 mb-12 p-1.5 bg-white/5 border border-white/10 rounded-2xl overflow-x-auto">
        {(['posting', 'system', 'apis'] as const).map((tab) => (
          <button
            key={tab}
            onClick={() => setActiveTab(tab)}
            className={`px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all whitespace-nowrap flex items-center gap-2 ${
              activeTab === tab 
                ? 'bg-sharp-orange text-black shadow-[0_0_15px_rgba(255,102,0,0.3)]' 
                : 'text-text-dim hover:text-white hover:bg-white/5'
            }`}
          >
            {tab === 'posting' && <Plus className="w-3 h-3" />}
            {tab === 'system' && <Settings className="w-3 h-3" />}
            {tab === 'apis' && <Key className="w-3 h-3" />}
            {tab}
          </button>
        ))}
      </div>

      {activeTab === 'system' && (
        <div className="bg-white/5 border border-white/10 p-10 rounded-[12px] space-y-8 mb-12">
          <h2 className="text-xs font-black text-sharp-orange uppercase tracking-[0.4em] flex items-center gap-2">
            <Settings className="w-4 h-4" /> Global Node Meta
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div className="space-y-2">
              <label className="text-[10px] font-black text-text-dim uppercase tracking-[0.2em]">App Title</label>
              <input 
                type="text" 
                value={appTitle}
                onChange={(e) => setAppTitle(e.target.value)}
                placeholder="e.g. CYBER-PULSE"
                className="w-full bg-black/40 border border-white/10 rounded-lg py-3 px-4 focus:border-sharp-orange outline-none transition-all font-medium"
              />
            </div>
            <div className="space-y-2">
              <label className="text-[10px] font-black text-text-dim uppercase tracking-[0.2em]">Hero Subheading</label>
              <input 
                type="text" 
                value={heroSubtext}
                onChange={(e) => setHeroSubtext(e.target.value)}
                placeholder="Talk about your projects..."
                className="w-full bg-black/40 border border-white/10 rounded-lg py-3 px-4 focus:border-sharp-orange outline-none transition-all font-medium"
              />
            </div>
          </div>

          <div className="space-y-4 pt-4 border-t border-white/5">
            <label className="text-[10px] font-black text-text-dim uppercase tracking-[0.2em]">Default AI Specialist</label>
            <div className="flex gap-4">
              {(['gemini', 'deepseek'] as const).map((p) => (
                <button
                  key={p}
                  onClick={() => setDefaultAI(p)}
                  className={`flex-1 py-3 rounded-xl border font-black text-[10px] uppercase tracking-widest transition-all ${defaultAI === p ? 'bg-glossy-purple border-glossy-purple text-white shadow-[0_0_15px_rgba(168,85,247,0.4)]' : 'border-white/10 bg-black/40 text-text-dim hover:border-white/30'}`}
                >
                  {p} Agent
                </button>
              ))}
            </div>
          </div>

          <button 
            onClick={handleSaveSettings}
            disabled={saveSettingsLoading}
            className="w-full py-4 bg-white/5 border border-white/10 hover:border-sharp-orange text-white font-bold rounded-lg flex items-center justify-center gap-2 transition-all uppercase tracking-widest text-[10px]"
          >
            {saveSettingsLoading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
            Update Global Sync
          </button>
        </div>
      )}

      {activeTab === 'apis' && (
        <div className="bg-white/5 border border-white/10 p-10 rounded-[12px] space-y-8 mb-12">
          <div className="flex items-center justify-between">
            <h2 className="text-xs font-black text-sharp-orange uppercase tracking-[0.4em] flex items-center gap-2">
              <Key className="w-4 h-4" /> Secure API Vault
            </h2>
            <div className="flex items-center gap-2 text-[10px] text-zinc-500 font-mono uppercase tracking-tighter">
              <Lock className="w-3 h-3" /> Encrypted At Rest
            </div>
          </div>

          <p className="text-[11px] text-text-dim font-mono leading-relaxed bg-black/40 border border-white/5 p-4 rounded-lg">
            These keys are stored in Firestore for dynamic runtime access. If an environment variable is set via the Cloud Console, it will take precedence. Leave blank to use system defaults.
          </p>

          <div className="space-y-6">
            <div className="space-y-2">
              <label className="text-[10px] font-black text-text-dim uppercase tracking-[0.2em]">Gemini API Key</label>
              <input 
                type="password" 
                value={apiKeys.gemini}
                onChange={(e) => setApiKeys({...apiKeys, gemini: e.target.value})}
                placeholder="••••••••••••••••"
                className="w-full bg-black/40 border border-white/10 rounded-lg py-3 px-4 focus:border-sharp-orange outline-none transition-all font-mono text-sm"
              />
            </div>
            <div className="space-y-2">
              <label className="text-[10px] font-black text-text-dim uppercase tracking-[0.2em]">DeepSeek API Key</label>
              <input 
                type="password" 
                value={apiKeys.deepseek}
                onChange={(e) => setApiKeys({...apiKeys, deepseek: e.target.value})}
                placeholder="••••••••••••••••"
                className="w-full bg-black/40 border border-white/10 rounded-lg py-3 px-4 focus:border-sharp-orange outline-none transition-all font-mono text-sm"
              />
            </div>
            <div className="space-y-2">
              <label className="text-[10px] font-black text-text-dim uppercase tracking-[0.2em]">Google PageSpeed API Key</label>
              <input 
                type="password" 
                value={apiKeys.pagespeed}
                onChange={(e) => setApiKeys({...apiKeys, pagespeed: e.target.value})}
                placeholder="••••••••••••••••"
                className="w-full bg-black/40 border border-white/10 rounded-lg py-3 px-4 focus:border-sharp-orange outline-none transition-all font-mono text-sm"
              />
            </div>
            <div className="pt-6 border-t border-white/5 space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="space-y-2">
                  <label className="text-[10px] font-black text-sharp-orange uppercase tracking-[0.2em]">Portal Username</label>
                  <div className="relative">
                    <UserIcon className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500" />
                    <input 
                      type="text" 
                      value={apiKeys.adminUsername}
                      onChange={(e) => setApiKeys({...apiKeys, adminUsername: e.target.value})}
                      placeholder="Admin Username"
                      className="w-full bg-black/40 border border-white/10 rounded-lg py-3 pl-12 pr-4 focus:border-sharp-orange outline-none transition-all font-mono text-sm"
                    />
                  </div>
                </div>
                <div className="space-y-2">
                  <label className="text-[10px] font-black text-sharp-orange uppercase tracking-[0.2em]">Portal Password</label>
                  <div className="relative">
                    <Lock className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500" />
                    <input 
                      type="password" 
                      value={apiKeys.adminPassword}
                      onChange={(e) => setApiKeys({...apiKeys, adminPassword: e.target.value})}
                      placeholder="New Master Password"
                      className="w-full bg-black/40 border border-white/10 rounded-lg py-3 pl-12 pr-4 focus:border-sharp-orange outline-none transition-all font-mono text-sm"
                    />
                  </div>
                </div>
              </div>
              <div className="space-y-2">
                <label className="text-[10px] font-black text-glossy-purple uppercase tracking-[0.2em]">Authorized Admin Email (Google Auth)</label>
                <div className="relative">
                  <UserIcon className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500" />
                  <input 
                    type="email" 
                    value={apiKeys.authorizedEmail}
                    onChange={(e) => setApiKeys({...apiKeys, authorizedEmail: e.target.value})}
                    placeholder="admin@example.com"
                    className="w-full bg-black/40 border border-white/10 rounded-lg py-3 pl-12 pr-4 focus:border-sharp-orange outline-none transition-all font-mono text-sm"
                  />
                </div>
                <p className="text-[9px] text-zinc-500 font-mono uppercase">Only this Google account will have write access to the grid.</p>
              </div>
            </div>
          </div>

          <button 
            onClick={handleSaveApiKeys}
            disabled={saveKeysLoading}
            className="w-full py-4 bg-glossy-purple text-white font-black rounded-lg flex items-center justify-center gap-2 transition-all uppercase tracking-widest text-[10px] shadow-[0_0_20px_rgba(168,85,247,0.2)] hover:shadow-[0_0_30px_rgba(168,85,247,0.4)]"
          >
            {saveKeysLoading ? <Loader2 className="w-4 h-4 animate-spin" /> : <ShieldCheck className="w-4 h-4" />}
            Commit Keys to Vault
          </button>
        </div>
      )}

      {activeTab === 'posting' && (
        <>
          <div className="bg-white/5 border border-white/10 p-10 rounded-[12px] space-y-8">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div className="space-y-2">
            <label className="text-[10px] font-black text-text-dim uppercase tracking-[0.2em]">Project URL</label>
            <div className="relative group/url">
              <LinkIcon className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
              <input 
                type="text" 
                value={url}
                onChange={(e) => setUrl(e.target.value)}
                placeholder="https://example.com"
                className="w-full bg-black/40 border border-white/10 rounded-lg py-3 pl-10 pr-24 focus:border-sharp-orange outline-none transition-all font-medium"
              />
              <button
                onClick={handleFetchScreenshot}
                disabled={isFetchingScreenshot || !url}
                className="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-white/5 hover:bg-white/10 border border-white/10 rounded-md text-[9px] font-black uppercase text-sharp-orange flex items-center gap-1.5 disabled:opacity-50 transition-all opacity-0 group-focus-within:opacity-100 group-hover:opacity-100"
              >
                {isFetchingScreenshot ? <Loader2 className="w-3 h-3 animate-spin" /> : <Zap className="w-3 h-3" />}
                Auto_Snap
              </button>
            </div>
          </div>

          <div className="space-y-2">
            <label className="text-[10px] font-black text-text-dim uppercase tracking-[0.2em]">Display Title (Optional)</label>
            <input 
              type="text" 
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder="e.g. My Awesome App"
              className="w-full bg-black/40 border border-white/10 rounded-lg py-3 px-4 focus:border-sharp-orange outline-none transition-all font-medium"
            />
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div className="space-y-2">
            <label className="text-[10px] font-black text-text-dim uppercase tracking-[0.2em]">Project Type</label>
            <div className="flex gap-4">
              <button 
                onClick={() => setType('web')}
                className={`flex-1 py-3 rounded-lg border font-bold text-xs uppercase tracking-widest flex items-center justify-center gap-2 transition-all ${type === 'web' ? 'bg-sharp-orange border-sharp-orange text-black' : 'border-white/10 bg-black/40 text-text-dim hover:border-white/30'}`}
              >
                <Globe className="w-4 h-4" /> SEO Web
              </button>
              <button 
                onClick={() => setType('app')}
                className={`flex-1 py-3 rounded-lg border font-bold text-xs uppercase tracking-widest flex items-center justify-center gap-2 transition-all ${type === 'app' ? 'bg-sharp-orange border-sharp-orange text-black' : 'border-white/10 bg-black/40 text-text-dim hover:border-white/30'}`}
              >
                <Smartphone className="w-4 h-4" /> App Build
              </button>
            </div>
          </div>

          <div className="space-y-2">
            <label className="text-[10px] font-black text-text-dim uppercase tracking-[0.2em]">Thumbnail Target (Image/Video)</label>
            <div className="flex gap-2">
              <input 
                type="text" 
                value={thumbnailUrl}
                onChange={(e) => setThumbnailUrl(e.target.value)}
                placeholder="URL or Upload ->"
                className="flex-1 bg-black/40 border border-white/10 rounded-lg py-3 px-4 focus:border-sharp-orange outline-none transition-all font-medium text-xs"
              />
              <label className="shrink-0 w-12 rounded-lg border border-dashed border-white/20 flex items-center justify-center cursor-pointer hover:border-sharp-orange transition-colors bg-white/5">
                <Plus className="w-4 h-4 text-text-dim" />
                <input 
                  type="file" 
                  className="hidden" 
                  accept="image/*,video/*"
                  onChange={(e) => {
                    const file = e.target.files?.[0];
                    if (file) {
                      const reader = new FileReader();
                      if (file.size > 800000) {
                        alert("FILE_OVERSIZE: Nodes are limited to ~800KB. Use an external URL for large assets.");
                        return;
                      }
                      reader.onload = (re) => setThumbnailUrl(re.target?.result as string);
                      reader.readAsDataURL(file);
                    }
                  }}
                />
              </label>
            </div>
            {thumbnailUrl && (
              <div className="mt-2 aspect-video rounded-md overflow-hidden bg-black/40 border border-white/5 relative group">
                <MediaRenderer src={thumbnailUrl} className="w-full h-full object-cover" />
                <button 
                  onClick={() => setThumbnailUrl('')}
                  className="absolute top-1 right-1 p-1 bg-black/60 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <Trash2 className="w-3 h-3 text-red-500" />
                </button>
              </div>
            )}
          </div>
        </div>

        {/* Gallery Section */}
        <div className="space-y-4 pt-4 border-t border-white/5">
          <div className="flex items-center justify-between">
            <label className="text-[10px] font-black text-text-dim uppercase tracking-[0.2em] flex items-center gap-2">
              <ImageIcon className="w-4 h-4 text-sharp-orange" /> Gallery Integration (Up to 5 images)
            </label>
            <span className="text-[9px] text-text-dim italic">Drag & drop images to auto-convert to Base64</span>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {galleryImages.map((img, idx) => (
              <div 
                key={idx} 
                className="relative group"
                onDragOver={(e) => e.preventDefault()}
                onDrop={(e) => handleFileDrop(e, idx)}
              >
                <div className="flex gap-2">
                  <div className="flex-1 space-y-2">
                    <input 
                      type="text" 
                      value={img}
                      onChange={(e) => {
                        const newGallery = [...galleryImages];
                        newGallery[idx] = e.target.value;
                        setGalleryImages(newGallery);
                      }}
                      placeholder={`Image URL or Drag Here`}
                      className="w-full bg-black/40 border border-white/10 rounded-lg py-3 px-4 focus:border-sharp-orange outline-none transition-all text-[11px]"
                    />
                    {uploadProgress[idx] !== undefined && (
                      <div className="w-full h-1 bg-white/5 rounded-full overflow-hidden">
                        <div 
                          className="h-full bg-sharp-orange transition-all duration-300" 
                          style={{ width: `${uploadProgress[idx]}%` }}
                        ></div>
                      </div>
                    )}
                  </div>
                  <label className="shrink-0 w-12 h-12 rounded-lg border border-dashed border-white/20 flex items-center justify-center cursor-pointer hover:border-sharp-orange transition-colors bg-white/5">
                    <Plus className="w-4 h-4 text-text-dim" />
                    <input 
                      type="file" 
                      className="hidden" 
                      accept="image/*,video/*"
                      onChange={(e) => {
                        const file = e.target.files?.[0];
                        if (file) handleMediaFile(file, idx);
                      }}
                    />
                  </label>
                </div>
                {img && (
                  <div className="mt-2 aspect-video rounded-md overflow-hidden bg-black/40 border border-white/5 relative">
                    <MediaRenderer src={img} className="w-full h-full object-cover" />
                    <button 
                      onClick={() => {
                        const newGallery = [...galleryImages];
                        newGallery[idx] = '';
                        setGalleryImages(newGallery);
                      }}
                      className="absolute top-1 right-1 p-1 bg-black/60 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <Trash2 className="w-3 h-3 text-red-500" />
                    </button>
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>

        {/* Demo Login Section */}
        <div className="space-y-4 pt-4 border-t border-white/5">
          <label className="text-[10px] font-black text-sharp-orange uppercase tracking-[0.2em] flex items-center gap-2">
            <Zap className="w-4 h-4" /> Multi-Tier Interface Access (One-Click Ready)
          </label>
          
          <div className="space-y-10">
            {/* Super Admin Tier */}
            <div className="space-y-4 bg-white/5 p-6 rounded-xl border border-white/5 border-l-4 border-sharp-orange">
              <div className="flex items-center justify-between">
                <span className="text-[11px] font-black uppercase italic tracking-widest text-sharp-orange">Level 0: Super Admin</span>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input 
                  type="text" 
                  value={accessPoints.superAdmin.url}
                  onChange={(e) => setAccessPoints({...accessPoints, superAdmin: {...accessPoints.superAdmin, url: e.target.value}})}
                  placeholder="Super Admin Login URL"
                  className="bg-black/40 border border-white/10 rounded-lg py-3 px-4 outline-none focus:border-sharp-orange transition-all text-xs"
                />
                <input 
                  type="text" 
                  value={accessPoints.superAdmin.directLoginUrl}
                  onChange={(e) => setAccessPoints({...accessPoints, superAdmin: {...accessPoints.superAdmin, directLoginUrl: e.target.value}})}
                  placeholder="Direct Bypass / One-Click Link"
                  className="bg-black/40 border border-white/10 rounded-lg py-3 px-4 outline-none focus:border-sharp-orange transition-all text-xs"
                />
                <input 
                  type="text" 
                  value={accessPoints.superAdmin.username}
                  onChange={(e) => setAccessPoints({...accessPoints, superAdmin: {...accessPoints.superAdmin, username: e.target.value}})}
                  placeholder="Username"
                  className="bg-black/40 border border-white/10 rounded-lg py-3 px-4 outline-none focus:border-sharp-orange transition-all text-xs"
                />
                <input 
                  type="text" 
                  value={accessPoints.superAdmin.password}
                  onChange={(e) => setAccessPoints({...accessPoints, superAdmin: {...accessPoints.superAdmin, password: e.target.value}})}
                  placeholder="Password"
                  className="bg-black/40 border border-white/10 rounded-lg py-3 px-4 outline-none focus:border-sharp-orange transition-all text-xs"
                />
              </div>
            </div>

            {/* Admin Tier */}
            <div className="space-y-4 bg-white/5 p-6 rounded-xl border border-white/5 border-l-4 border-glossy-purple">
              <div className="flex items-center justify-between">
                <span className="text-[11px] font-black uppercase italic tracking-widest text-glossy-purple">Level 1: Restricted Admin</span>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input 
                  type="text" 
                  value={accessPoints.admin.url}
                  onChange={(e) => setAccessPoints({...accessPoints, admin: {...accessPoints.admin, url: e.target.value}})}
                  placeholder="Admin Login URL"
                  className="bg-black/40 border border-white/10 rounded-lg py-3 px-4 outline-none focus:border-glossy-purple transition-all text-xs"
                />
                <input 
                  type="text" 
                  value={accessPoints.admin.directLoginUrl}
                  onChange={(e) => setAccessPoints({...accessPoints, admin: {...accessPoints.admin, directLoginUrl: e.target.value}})}
                  placeholder="Direct Bypass / One-Click Link"
                  className="bg-black/40 border border-white/10 rounded-lg py-3 px-4 outline-none focus:border-glossy-purple transition-all text-xs"
                />
                <input 
                  type="text" 
                  value={accessPoints.admin.username}
                  onChange={(e) => setAccessPoints({...accessPoints, admin: {...accessPoints.admin, username: e.target.value}})}
                  placeholder="Username"
                  className="bg-black/40 border border-white/10 rounded-lg py-3 px-4 outline-none focus:border-glossy-purple transition-all text-xs"
                />
                <input 
                  type="text" 
                  value={accessPoints.admin.password}
                  onChange={(e) => setAccessPoints({...accessPoints, admin: {...accessPoints.admin, password: e.target.value}})}
                  placeholder="Password"
                  className="bg-black/40 border border-white/10 rounded-lg py-3 px-4 outline-none focus:border-glossy-purple transition-all text-xs"
                />
              </div>
            </div>

            {/* User Tier */}
            <div className="space-y-4 bg-white/5 p-6 rounded-xl border border-white/5 border-l-4 border-blue-500">
              <div className="flex items-center justify-between">
                <span className="text-[11px] font-black uppercase italic tracking-widest text-blue-400">Level 2: Standard User</span>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input 
                  type="text" 
                  value={accessPoints.user.url}
                  onChange={(e) => setAccessPoints({...accessPoints, user: {...accessPoints.user, url: e.target.value}})}
                  placeholder="User Login URL"
                  className="bg-black/40 border border-white/10 rounded-lg py-3 px-4 outline-none focus:border-blue-500 transition-all text-xs"
                />
                <input 
                  type="text" 
                  value={accessPoints.user.directLoginUrl}
                  onChange={(e) => setAccessPoints({...accessPoints, user: {...accessPoints.user, directLoginUrl: e.target.value}})}
                  placeholder="Direct Bypass / One-Click Link"
                  className="bg-black/40 border border-white/10 rounded-lg py-3 px-4 outline-none focus:border-blue-500 transition-all text-xs"
                />
                <input 
                  type="text" 
                  value={accessPoints.user.username}
                  onChange={(e) => setAccessPoints({...accessPoints, user: {...accessPoints.user, username: e.target.value}})}
                  placeholder="Username"
                  className="bg-black/40 border border-white/10 rounded-lg py-3 px-4 outline-none focus:border-blue-500 transition-all text-xs"
                />
                <input 
                  type="text" 
                  value={accessPoints.user.password}
                  onChange={(e) => setAccessPoints({...accessPoints, user: {...accessPoints.user, password: e.target.value}})}
                  placeholder="Password"
                  className="bg-black/40 border border-white/10 rounded-lg py-3 px-4 outline-none focus:border-blue-500 transition-all text-xs"
                />
              </div>
            </div>
          </div>

          <div className="pt-8 border-t border-white/5">
            <label className="text-[10px] font-black text-text-dim uppercase tracking-[0.2em] flex items-center gap-2">
              <ShieldCheck className="w-4 h-4 text-sharp-orange" /> Legacy Note Payload
            </label>
            <input 
              type="text" 
              value={demoLogin.note}
              onChange={(e) => setDemoLogin({...demoLogin, note: e.target.value})}
              placeholder="Note (e.g. Sandbox mode)"
              className="mt-4 w-full bg-black/40 border border-white/10 rounded-lg py-3 px-4 focus:border-sharp-orange outline-none transition-all text-sm"
            />
          </div>
        </div>

        <div className="flex items-center gap-4 pt-4 border-t border-white/5">
          <button 
            onClick={() => setIsPinned(!isPinned)}
            className={`flex items-center gap-3 px-6 py-3 rounded-lg border font-black text-[10px] uppercase tracking-widest transition-all ${isPinned ? 'bg-sharp-orange border-sharp-orange text-black' : 'border-white/10 text-text-dim hover:border-white/30'}`}
          >
            <Pin className={`w-4 h-4 ${isPinned ? 'fill-current' : ''}`} />
            {isPinned ? 'PINNED_TO_HERO' : 'PIN_TO_HERO'}
          </button>
          <span className="text-[9px] text-text-dim italic">Pinned projects are prioritized in the hero section. Max 4 slots recommended.</span>
        </div>

        <div className="flex flex-col gap-6 pt-6 border-t border-white/5">
          <div className="space-y-3">
             <label className="text-[10px] font-black text-text-dim uppercase tracking-[0.2em]">Active AI Analysis Node</label>
             <div className="flex gap-2">
               {(['gemini', 'deepseek'] as const).map((p) => (
                 <button
                   key={p}
                   onClick={() => setActiveAI(p)}
                   className={`flex-1 py-2.5 rounded-lg border font-black text-[9px] uppercase tracking-widest transition-all ${activeAI === p ? 'bg-glossy-purple border-glossy-purple text-white shadow-[0_0_15px_rgba(168,85,247,0.3)]' : 'border-white/10 bg-black/40 text-text-dim hover:border-white/30'}`}
                 >
                   {p} ENGINE
                 </button>
               ))}
             </div>
          </div>
          
          <div className="flex gap-4">
            <button 
              onClick={handleMagicGenerate}
              disabled={loading || !url}
              className="flex-1 py-5 bg-sharp-orange text-black font-black rounded-xl flex items-center justify-center gap-3 neon-orange hover:brightness-110 active:scale-95 transition-all disabled:opacity-50 disabled:scale-100 uppercase tracking-widest text-sm"
            >
              {loading ? <Loader2 className="w-5 h-5 animate-spin" /> : <Sparkles className="w-5 h-5" />}
              {activeAI.toUpperCase()} SCAN & DEPLOY
            </button>
            <button 
              onClick={handleManualEntry}
              className="px-8 py-5 bg-white/5 border border-white/10 text-white font-black rounded-xl hover:bg-white/10 active:scale-95 transition-all uppercase tracking-widest text-sm"
            >
              Manual_Mode
            </button>
          </div>
        </div>
      </div>

      {generatedData && (
        <div className="mt-12 grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-8 animate-in fade-in slide-in-from-bottom-5 duration-700">
          <div className="space-y-8">
            {/* Screenshot Preview */}
            <div className="bg-white/5 border border-white/10 p-2 rounded-[12px] overflow-hidden group">
              <div className="aspect-video relative rounded-lg overflow-hidden bg-black/40 border border-white/5 flex items-center justify-center">
                {thumbnailUrl ? (
                  <img 
                    src={thumbnailUrl} 
                    alt="Screenshot Preview" 
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    referrerPolicy="no-referrer"
                    loading="lazy"
                  />
                ) : (
                  <div className="flex flex-col items-center gap-2">
                    <ImageIcon className="w-8 h-8 text-white/10" />
                    <span className="text-[9px] font-mono text-text-dim uppercase">Standby for snapshot...</span>
                  </div>
                )}
                <div className="absolute inset-x-0 bottom-0 p-4 bg-black/60 backdrop-blur-sm">
                  <div className="text-[10px] font-black text-white uppercase tracking-widest flex items-center gap-2">
                    <ImageIcon className="w-3 h-3 text-sharp-orange" /> Interface Snapshot Mapped
                  </div>
                </div>
              </div>
            </div>

            <div className="bg-white/5 border border-white/10 p-8 rounded-[12px] space-y-4">
              <label className="text-[10px] font-black text-sharp-orange uppercase tracking-[0.2em]">Live Script / Content</label>
              <textarea 
                value={generatedData.content}
                onChange={(e) => setGeneratedData({ ...generatedData, content: e.target.value })}
                rows={10}
                className="w-full bg-black/40 border border-white/10 rounded-lg py-4 px-4 focus:border-sharp-orange outline-none transition-all font-medium text-[14px] leading-relaxed text-white/90"
              />
              <div className="flex items-center gap-2 opacity-40">
                <Sparkles className="w-3 h-3 text-glossy-purple" />
                <span className="text-[9px] font-bold uppercase tracking-widest">Powered by Gemini AI (Post-Generation Layer)</span>
              </div>
            </div>

            <div className="bg-white/5 border border-white/10 p-8 rounded-[12px] space-y-4">
              <h2 className="text-[10px] font-black text-sharp-orange uppercase tracking-[0.2em]">WhatsApp Direct Payload</h2>
              <textarea 
                value={generatedData.waMessage}
                onChange={(e) => setGeneratedData({ ...generatedData, waMessage: e.target.value })}
                rows={4}
                className="w-full bg-black/40 border border-white/10 rounded-lg py-4 px-4 focus:border-sharp-orange outline-none transition-all font-mono text-[12px] text-text-dim"
              />
            </div>
          </div>

          <div className="space-y-8">
            <div className="bg-white/5 border-l-4 border-sharp-orange p-6 rounded-[12px] space-y-6">
              <h2 className="text-[10px] font-black text-sharp-orange uppercase tracking-[0.2em]">SEO Metadata Tuning</h2>
              <div className="space-y-4">
                <div>
                  <label className="block text-[9px] text-text-dim mb-1 font-bold italic uppercase">Title_Tag</label>
                  <input 
                    type="text"
                    value={generatedData.seoData?.metaTitle}
                    onChange={(e) => setGeneratedData({ 
                      ...generatedData, 
                      seoData: { ...generatedData.seoData!, metaTitle: e.target.value } 
                    })}
                    className="w-full bg-black/40 border border-white/10 rounded py-2 px-3 text-[12px] focus:border-sharp-orange outline-none"
                  />
                </div>
                <div>
                  <label className="block text-[9px] text-text-dim mb-1 font-bold italic uppercase">Description_Meta</label>
                  <textarea 
                    value={generatedData.seoData?.metaDescription}
                    onChange={(e) => setGeneratedData({ 
                      ...generatedData, 
                      seoData: { ...generatedData.seoData!, metaDescription: e.target.value } 
                    })}
                    rows={4}
                    className="w-full bg-black/40 border border-white/10 rounded py-2 px-3 text-[11px] focus:border-sharp-orange outline-none"
                  />
                </div>
              </div>
            </div>

            <div className="bg-white/5 border border-white/10 p-6 rounded-[12px] space-y-4">
              <h2 className="text-[10px] font-black text-sharp-orange uppercase tracking-[0.2em]">Interface Tech Stack</h2>
              <div className="flex flex-wrap gap-2">
                {generatedData.techStack?.map((t, i) => (
                  <div key={i} className="flex items-center">
                    <input 
                      type="text"
                      value={t.name}
                      onChange={(e) => {
                        const newStack = [...generatedData.techStack!];
                        newStack[i] = { ...newStack[i], name: e.target.value };
                        setGeneratedData({ ...generatedData, techStack: newStack });
                      }}
                      className="px-2 py-1 font-mono text-[11px] bg-glossy-purple/10 border border-glossy-purple/30 rounded text-[#E0A0FF] w-24 outline-none focus:border-glossy-purple transition-all"
                    />
                  </div>
                ))}
                <button 
                  onClick={() => {
                    const newStack = [...(generatedData.techStack || []), { name: 'NEW_TECH' }];
                    setGeneratedData({ ...generatedData, techStack: newStack });
                  }}
                  className="px-3 py-1 font-mono text-[11px] bg-white/5 border border-dashed border-white/20 rounded text-text-dim hover:text-white transition-all"
                >
                  + ADD_NODE
                </button>
              </div>
            </div>

            <button 
              onClick={handleSave}
              disabled={loading}
              className="w-full py-5 bg-sharp-orange text-black font-black rounded-xl flex items-center justify-center gap-2 neon-orange hover:brightness-110 active:scale-95 transition-all text-sm uppercase tracking-widest"
            >
              <Save className="w-5 h-5" /> {editingId ? 'UPDATE_NODE' : 'PUBLISH_NODE'}
            </button>
          </div>
        </div>
      )}

      <div className="mt-20 border-t border-white/10 pt-12 space-y-8">
          <h2 className="text-xl font-black italic uppercase tracking-tighter text-glow-orange">
            GRID <span className="text-sharp-orange">NODE_CONTROL</span>
          </h2>
          
          <div className="grid grid-cols-1 gap-4">
            {allProjects.map((proj) => (
              <div key={proj.id} className="bg-white/5 border border-white/10 p-4 rounded-xl flex flex-col md:flex-row items-center justify-between gap-4 group">
                <div className="flex items-center gap-4 w-full md:w-auto">
                  <div className="w-12 h-12 rounded-lg bg-black border border-white/10 overflow-hidden shrink-0 flex items-center justify-center">
                    {proj.thumbnailUrl ? (
                      <img 
                        src={proj.thumbnailUrl} 
                        className="w-full h-full object-cover" 
                        referrerPolicy="no-referrer" 
                        loading="lazy"
                      />
                    ) : (
                      <ImageIcon className="w-4 h-4 text-white/20" />
                    )}
                  </div>
                  <div>
                    <div className="text-sm font-bold text-white uppercase tracking-tight">{proj.title}</div>
                    <div className="text-[10px] text-text-dim font-mono uppercase italic">{proj.slug}</div>
                  </div>
                </div>
                
                <div className="flex items-center gap-2 w-full md:w-auto justify-end border-t md:border-t-0 border-white/5 pt-3 md:pt-0">
                  <button 
                    onClick={() => handleEditClick(proj)}
                    className={`p-2 rounded-lg transition-all ${editingId === proj.id ? 'bg-glossy-purple text-white' : 'text-text-dim hover:text-white bg-white/5'}`}
                    title="Modify Node"
                  >
                    <Pencil className="w-4 h-4" />
                  </button>
                  <button 
                    onClick={() => handleTogglePin(proj)}
                    className={`p-2 rounded-lg transition-all ${proj.isPinned ? 'bg-sharp-orange text-black' : 'text-text-dim hover:text-white bg-white/5'}`}
                    title={proj.isPinned ? 'Unpin from Hero' : 'Pin to Hero'}
                  >
                    <Pin className={`w-4 h-4 ${proj.isPinned ? 'fill-current' : ''}`} />
                  </button>

                  {deleteId === proj.id ? (
                    <div className="flex items-center gap-2 bg-red-500/10 border border-red-500/50 rounded-lg p-1 animate-pulse">
                      <span className="text-[9px] font-black text-red-500 px-2 uppercase shadow-red-500/20">Purge?</span>
                      <button 
                        onClick={() => handleDeleteProject(proj.id)}
                        disabled={isDeleting}
                        className="p-1 px-3 bg-red-500 text-white rounded text-[9px] font-bold uppercase hover:bg-red-600 transition-colors disabled:opacity-50"
                      >
                        {isDeleting ? 'Erasing...' : 'Confirm'}
                      </button>
                      <button 
                        onClick={() => setDeleteId(null)}
                        disabled={isDeleting}
                        className="p-1 px-3 bg-white/10 text-white rounded text-[9px] font-bold uppercase hover:bg-white/20 transition-colors"
                      >
                        Abort
                      </button>
                    </div>
                  ) : (
                    <button 
                      onClick={() => setDeleteId(proj.id)}
                      className="p-2 rounded-lg bg-white/5 text-text-dim hover:text-red-500 hover:bg-red-500/10 transition-all"
                      title="Purge Node"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  )}
                </div>
              </div>
            ))}
            {allProjects.length === 0 && (
              <div className="py-20 text-center text-text-dim font-mono text-sm uppercase tracking-widest border border-dashed border-white/10 rounded-xl">
                NO_ACTIVE_NODES_FOUND
              </div>
            )}
          </div>
        </div>
      </>)}
    </div>
  );
}
