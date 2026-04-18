
import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { collection, query, where, getDocs, updateDoc, doc, increment } from 'firebase/firestore';
import { db } from '../lib/firebase';
import { Project } from '../types';
import { 
  Globe, 
  Smartphone, 
  MessageSquare, 
  ArrowLeft, 
  ShieldCheck, 
  Zap, 
  Activity,
  Share2,
  ExternalLink,
  ChevronRight,
  Sparkles,
  Play,
  Image as ImageIcon,
  Key,
  Info,
  X,
  Copy,
  Check,
  Twitter,
  Linkedin,
  Facebook,
  Search as SearchIcon,
  Tag,
  ZoomIn,
  ZoomOut,
  Link as LinkIcon,
  Crown,
  User,
  Shield,
  Key as KeyIcon,
  Terminal,
  MousePointer2
} from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';
import ReactMarkdown from 'react-markdown';
import MediaRenderer from '../components/MediaRenderer';

export default function ProjectDetail() {
  const { slug } = useParams();
  const [project, setProject] = useState<Project | null>(null);
  const [loading, setLoading] = useState(true);
  const [viewMode, setViewMode] = useState<'desktop' | 'mobile'>('desktop');
  const [previewTab, setPreviewTab] = useState<'live' | 'gallery'>('live');
  const [activeGalleryIdx, setActiveGalleryIdx] = useState(0);
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [copiedField, setCopiedField] = useState<string | null>(null);
  const [zoomLevel, setZoomLevel] = useState(1);

  useEffect(() => {
    const fetchProject = async () => {
      const q = query(collection(db, 'projects'), where('slug', '==', slug));
      const snapshot = await getDocs(q);
      if (!snapshot.empty) {
        setProject({ id: snapshot.docs[0].id, ...snapshot.docs[0].data() } as Project);
      }
      setLoading(false);
    };
    fetchProject();
  }, [slug]);

  const handleWAClick = async () => {
    if (!project) return;
    try {
      await updateDoc(doc(db, 'projects', project.id), {
        inquiriesCount: increment(1)
      });
      const encodedMsg = encodeURIComponent(project.waMessage);
      window.open(`https://wa.me/2348123456789?text=${encodedMsg}`, '_blank');
    } catch (e) {
      console.error(e);
    }
  };

  const handleCopy = (text: string, field: string) => {
    navigator.clipboard.writeText(text);
    setCopiedField(field);
    setTimeout(() => setCopiedField(null), 2000);
  };

  const handleOneClickLogin = (access: any) => {
    if (access.directLoginUrl) {
      window.open(access.directLoginUrl, '_blank');
    } else if (access.url) {
      if (access.password) {
        navigator.clipboard.writeText(access.password);
        setCopiedField('access-pass');
        setTimeout(() => setCopiedField(null), 3000);
      }
      window.open(access.url, '_blank');
    }
  };

  if (loading) return <div className="py-20 text-center text-sharp-orange animate-pulse">SYNCING ENGINES...</div>;
  if (!project) return <div className="py-20 text-center font-bold text-gray-500">PROJECT NOT FOUND.</div>;

  const shareUrl = window.location.href;
  const shareText = `Check out ${project.title} - ${project.seoData?.metaDescription || ''}`;

  const shareLinks = [
    { name: 'Twitter', icon: Twitter, url: `https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareText)}`, color: 'hover:text-[#1DA1F2]' },
    { name: 'LinkedIn', icon: Linkedin, url: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareUrl)}`, color: 'hover:text-[#0A66C2]' },
    { name: 'Facebook', icon: Facebook, url: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`, color: 'hover:text-[#1877F2]' },
  ];

  return (
    <div className="space-y-8 pb-20 mt-4">
      {/* Lightbox Modal */}
      <AnimatePresence>
        {lightboxOpen && (
          <motion.div 
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-[100] bg-black/95 flex items-center justify-center p-4 md:p-10"
            onClick={() => setLightboxOpen(false)}
          >
            <button 
              className="absolute top-10 right-10 text-white/50 hover:text-white transition-colors"
              onClick={() => setLightboxOpen(false)}
            >
              <X className="w-10 h-10" />
            </button>
            {project.galleryImages?.[activeGalleryIdx] ? (
              <motion.img 
                initial={{ scale: 0.9, opacity: 0 }}
                animate={{ scale: 1, opacity: 1 }}
                exit={{ scale: 0.9, opacity: 0 }}
                src={project.galleryImages[activeGalleryIdx]} 
                alt="Gallery Full"
                className="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
                onClick={(e) => e.stopPropagation()}
              />
            ) : (
              <div className="text-text-dim font-mono uppercase text-xs">Node Image Unavailable</div>
            )}
          </motion.div>
        )}
      </AnimatePresence>
      {/* Header Info */}
      <div className="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 h-auto">
        <div className="space-y-2">
          <Link to="/" className="inline-flex items-center gap-2 text-[11px] font-mono text-text-dim hover:text-sharp-orange transition-colors uppercase tracking-[0.2em]">
            <ArrowLeft className="w-3 h-3" /> Back to Grid
          </Link>
          <div className="flex items-center gap-4">
            <h1 className="text-4xl md:text-5xl font-black italic tracking-tighter uppercase whitespace-nowrap">
              {project.title}
            </h1>
            <button 
              onClick={() => handleCopy(project.url || '', 'proj-url')}
              className="p-2 rounded-lg bg-white/5 border border-white/10 text-text-dim hover:text-sharp-orange transition-all relative group"
              title="Copy Interface URL"
            >
              {copiedField === 'proj-url' ? <Check className="w-4 h-4" /> : <LinkIcon className="w-4 h-4" />}
              <span className="absolute -bottom-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-black text-[9px] font-bold uppercase rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap border border-white/10 z-50">
                Copy Link
              </span>
            </button>
          </div>
          <div className="flex items-center gap-4">
            <div className={`px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase flex items-center gap-2 border ${project.type === 'web' ? 'border-sharp-orange/20 text-sharp-orange' : 'border-glossy-purple/20 text-glossy-purple'}`}>
              {project.type === 'web' ? <Globe className="w-3 h-3" /> : <Smartphone className="w-3 h-3" />}
              {project.type === 'web' ? 'Web Solution' : 'App Engineering'}
            </div>
          </div>
        </div>
        
        <div className="text-[11px] text-text-dim font-mono hidden md:block">
          CODEX_ID: PB_{project.id.slice(0, 4).toUpperCase()} // LATENCY: 24ms // ENCRYPTION: AES-256
        </div>
      </div>

      {/* Main Content Grid: Match blueprint split layout */}
      <div className="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-[20px]">
        
        {/* Left Section: Iframe Preview Container */}
        <div className="flex flex-col bg-white/5 border border-white/10 rounded-[12px] overflow-hidden">
          <div className="h-[40px] border-b border-white/10 px-[15px] flex items-center justify-between">
            <div className="flex items-center gap-[8px]">
              <div className="w-2 h-2 rounded-full bg-white/20"></div>
              <div className="w-2 h-2 rounded-full bg-white/20"></div>
              <div className="w-2 h-2 rounded-full bg-white/20"></div>
              <div className="ml-5 text-[11px] font-mono text-text-dim truncate max-w-[200px] md:max-w-md">{project.url}</div>
            </div>
            {project.galleryImages && project.galleryImages.length > 0 && (
              <div className="flex bg-black/40 p-1 rounded-md border border-white/5">
                <button 
                  onClick={() => setPreviewTab('live')}
                  className={`px-3 py-1 rounded text-[9px] font-bold uppercase transition-all flex items-center gap-1.5 ${previewTab === 'live' ? 'bg-sharp-orange text-black' : 'text-text-dim hover:text-white'}`}
                >
                  <Play className="w-2.5 h-2.5" /> Live
                </button>
                <button 
                  onClick={() => setPreviewTab('gallery')}
                  className={`px-3 py-1 rounded text-[9px] font-bold uppercase transition-all flex items-center gap-1.5 ${previewTab === 'gallery' ? 'bg-sharp-orange text-black' : 'text-text-dim hover:text-white'}`}
                >
                  <ImageIcon className="w-2.5 h-2.5" /> Gallery
                </button>
              </div>
            )}
          </div>
          
          <div className="flex-1 bg-[#111] m-[15px] rounded-[4px] border border-white/5 relative overflow-hidden group min-h-[500px] md:h-[580px] lg:h-[700px] h-[70vh]">
            <div className="absolute top-4 right-4 z-20 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
              <button 
                onClick={() => setZoomLevel(prev => Math.min(prev + 0.1, 2))}
                className="p-2 bg-black/60 backdrop-blur-md rounded border border-white/10 text-white hover:text-sharp-orange transition-colors"
                title="Zoom In"
              >
                <ZoomIn className="w-4 h-4" />
              </button>
              <button 
                onClick={() => setZoomLevel(prev => Math.max(prev - 0.1, 0.5))}
                className="p-2 bg-black/60 backdrop-blur-md rounded border border-white/10 text-white hover:text-sharp-orange transition-colors"
                title="Zoom Out"
              >
                <ZoomOut className="w-4 h-4" />
              </button>
              <button 
                onClick={() => setZoomLevel(1)}
                className="p-2 bg-black/60 backdrop-blur-md rounded border border-white/10 text-[9px] font-bold text-white uppercase"
                title="Reset Zoom"
              >
                Reset
              </button>
            </div>

            <AnimatePresence mode="wait">
              {previewTab === 'live' ? (
                <motion.div 
                  key="live"
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  exit={{ opacity: 0 }}
                  className="w-full h-full"
                  style={{ transform: `scale(${zoomLevel})`, transformOrigin: 'center center', transition: 'transform 0.2s ease-out' }}
                >
                  {project.type === 'app' ? (
                    <div className="w-full h-full flex items-center justify-center p-4 md:p-8">
                      <div className="relative z-10 w-[280px] h-[85%] max-h-[750px] border-[8px] border-white/10 rounded-[2.5rem] overflow-hidden bg-black shadow-2xl">
                         <iframe 
                          src={project.url} 
                          className="w-full h-full border-0" 
                          title="Mobile Preview"
                        />
                      </div>
                    </div>
                  ) : (
                    <div className={`transition-all duration-500 h-full mx-auto ${viewMode === 'desktop' ? 'w-full' : 'w-full max-w-[375px] border-x border-white/10'}`}>
                      <iframe 
                        src={project.url} 
                        className="w-full h-full border-0" 
                        title="Site Preview"
                      />
                    </div>
                  )}
                </motion.div>
              ) : (
                <motion.div 
                  key="gallery"
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  exit={{ opacity: 0 }}
                  className="w-full h-full p-[30px] flex flex-col gap-[20px]"
                >
                  <div 
                    className="flex-1 rounded-[8px] overflow-hidden border border-white/10 bg-black/40 relative cursor-zoom-in"
                    onClick={() => project.galleryImages?.[activeGalleryIdx] && setLightboxOpen(true)}
                  >
                    {project.galleryImages?.[activeGalleryIdx] ? (
                      <MediaRenderer 
                        src={project.galleryImages[activeGalleryIdx]} 
                        alt={`Gallery ${activeGalleryIdx + 1}`}
                        className="w-full h-full object-contain transition-transform duration-700"
                        style={{ transform: `scale(${zoomLevel})` }}
                        onClick={() => project.galleryImages?.[activeGalleryIdx] && setLightboxOpen(true)}
                      />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center text-text-dim font-mono text-[10px] uppercase">
                        STANDBY_FOR_VISUALS...
                      </div>
                    )}
                    <div className="absolute inset-0 bg-black/0 transition-colors flex items-center justify-center pointer-events-none group-hover:bg-black/10">
                      <div className="bg-black/60 p-4 rounded-full border border-white/10 opacity-0 group-hover:opacity-100 transition-opacity">
                        <ImageIcon className="w-6 h-6 text-white" />
                      </div>
                    </div>
                  </div>
                  <div className="flex justify-center gap-4">
                    {project.galleryImages?.map((img, idx) => (
                      <button
                        key={idx}
                        onClick={() => setActiveGalleryIdx(idx)}
                        className={`w-16 h-10 rounded border transition-all overflow-hidden ${activeGalleryIdx === idx ? 'border-sharp-orange scale-110' : 'border-white/10 opacity-50 hover:opacity-100'}`}
                      >
                        {img ? (
                          <MediaRenderer src={img} className="w-full h-full object-cover" />
                        ) : (
                          <div className="w-full h-full bg-white/5 flex items-center justify-center">
                            <ImageIcon className="w-3 h-3 text-white/20" />
                          </div>
                        )}
                      </button>
                    ))}
                  </div>
                </motion.div>
              )}
            </AnimatePresence>
          </div>

          <div className="p-[15px] flex gap-[20px] justify-center border-top border-white/5">
            <button 
              onClick={() => setViewMode('desktop')}
              className={`text-[12px] font-bold uppercase transition-colors ${viewMode === 'desktop' ? 'text-sharp-orange' : 'text-text-dim hover:text-white'}`}
            >
              ● DESKTOP VIEW
            </button>
            <button 
              onClick={() => setViewMode('mobile')}
              className={`text-[12px] font-bold uppercase transition-colors ${viewMode === 'mobile' ? 'text-sharp-orange' : 'text-text-dim hover:text-white'}`}
            >
              {viewMode === 'mobile' && '● '}MOBILE VIEW
            </button>
          </div>
        </div>

        {/* Right Section: Sidebar */}
        <div className="flex flex-col gap-[20px]">
          {/* One-Click Access Tiers */}
          {project.accessPoints && (Object.values(project.accessPoints).some(v => v)) && (
            <div className="bg-white/5 border border-white/10 p-6 rounded-[12px] space-y-6 relative overflow-hidden">
              <div className="absolute top-0 right-0 w-32 h-32 bg-sharp-orange/5 blur-3xl -mr-16 -mt-16"></div>
              
              <div className="flex items-center justify-between border-b border-white/10 pb-3">
                <span className="text-[11px] font-black uppercase tracking-[0.3em] text-sharp-orange flex items-center gap-2">
                  <Terminal className="w-3 h-3" /> One-Click Access Nodes
                </span>
                <AnimatePresence>
                  {copiedField === 'access-pass' && (
                    <motion.span 
                      initial={{ opacity: 0, x: 10 }}
                      animate={{ opacity: 1, x: 0 }}
                      exit={{ opacity: 0 }}
                      className="text-[9px] font-mono text-sharp-orange uppercase animate-pulse"
                    >
                      Password Copied!
                    </motion.span>
                  )}
                </AnimatePresence>
              </div>

              <div className="grid grid-cols-1 gap-3">
                {project.accessPoints.superAdmin && (
                  <button 
                    onClick={() => handleOneClickLogin(project.accessPoints?.superAdmin)}
                    className="group flex items-center justify-between bg-black/40 p-4 rounded-xl border border-white/5 hover:border-sharp-orange transition-all relative overflow-hidden"
                  >
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 rounded-lg bg-sharp-orange/10 flex items-center justify-center border border-sharp-orange/20">
                        <Crown className="w-4 h-4 text-sharp-orange" />
                      </div>
                      <div className="text-left">
                        <div className="text-[10px] font-black text-white uppercase tracking-widest">Level 0: Super Admin</div>
                        <div className="text-[9px] text-text-dim font-mono uppercase italic">Bypass Enabled</div>
                      </div>
                    </div>
                    <MousePointer2 className="w-4 h-4 text-text-dim group-hover:text-sharp-orange transition-colors" />
                  </button>
                )}

                {project.accessPoints.admin && (
                  <button 
                    onClick={() => handleOneClickLogin(project.accessPoints?.admin)}
                    className="group flex items-center justify-between bg-black/40 p-4 rounded-xl border border-white/5 hover:border-glossy-purple transition-all relative overflow-hidden"
                  >
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 rounded-lg bg-glossy-purple/10 flex items-center justify-center border border-glossy-purple/20">
                        <Shield className="w-4 h-4 text-glossy-purple" />
                      </div>
                      <div className="text-left">
                        <div className="text-[10px] font-black text-white uppercase tracking-widest">Level 1: Restricted Admin</div>
                        <div className="text-[9px] text-text-dim font-mono uppercase italic">Admin Console Access</div>
                      </div>
                    </div>
                    <MousePointer2 className="w-4 h-4 text-text-dim group-hover:text-glossy-purple transition-colors" />
                  </button>
                )}

                {project.accessPoints.user && (
                    <button 
                    onClick={() => handleOneClickLogin(project.accessPoints?.user)}
                    className="group flex items-center justify-between bg-black/40 p-4 rounded-xl border border-white/5 hover:border-blue-500 transition-all relative overflow-hidden"
                  >
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center border border-blue-500/20">
                        <User className="w-4 h-4 text-blue-400" />
                      </div>
                      <div className="text-left">
                        <div className="text-[10px] font-black text-white uppercase tracking-widest">Level 2: Standard User</div>
                        <div className="text-[9px] text-text-dim font-mono uppercase italic">Client Portal Active</div>
                      </div>
                    </div>
                    <MousePointer2 className="w-4 h-4 text-text-dim group-hover:text-blue-400 transition-colors" />
                  </button>
                )}
              </div>
              
              <div className="pt-2 flex items-center gap-2 opacity-40">
                <Sparkles className="w-3 h-3 text-sharp-orange" />
                <span className="text-[8px] font-mono uppercase tracking-[0.2em] text-white">Advanced Session Tunneling Protocol</span>
              </div>
            </div>
          )}

          {/* Demo Login Alert */}
          {project.demoLogin && (
            <div className="bg-sharp-orange/10 border border-sharp-orange/30 p-5 rounded-[12px] space-y-3">
              <div className="flex items-center gap-2 text-[10px] font-black text-sharp-orange uppercase tracking-widest">
                <ShieldCheck className="w-4 h-4" /> Demo Access Node
              </div>
              <div className="grid grid-cols-1 gap-2">
                <div className="flex items-center justify-between bg-black/40 p-2 rounded border border-white/5 group relative">
                  <span className="text-[9px] text-text-dim uppercase font-mono">User</span>
                  <div className="flex items-center gap-2">
                    <span className="text-[11px] font-bold font-mono text-white select-all">{project.demoLogin.username}</span>
                    <button 
                      onClick={() => handleCopy(project.demoLogin?.username || '', 'user')}
                      className="text-text-dim hover:text-sharp-orange transition-colors"
                    >
                      {copiedField === 'user' ? <Check className="w-3 h-3" /> : <Copy className="w-3 h-3" />}
                    </button>
                  </div>
                </div>
                <div className="flex items-center justify-between bg-black/40 p-2 rounded border border-white/5 group relative">
                  <span className="text-[9px] text-text-dim uppercase font-mono">Pass</span>
                  <div className="flex items-center gap-2">
                    <span className="text-[11px] font-bold font-mono text-white select-all">{project.demoLogin.password}</span>
                    <button 
                      onClick={() => handleCopy(project.demoLogin?.password || '', 'pass')}
                      className="text-text-dim hover:text-sharp-orange transition-colors"
                    >
                      {copiedField === 'pass' ? <Check className="w-3 h-3" /> : <Copy className="w-3 h-3" />}
                    </button>
                  </div>
                </div>
              </div>
              {project.demoLogin.note && (
                <div className="flex gap-2 bg-black/20 p-2 rounded border border-white/5">
                  <Info className="w-3 h-3 text-text-dim shrink-0 mt-0.5" />
                  <p className="text-[10px] text-text-dim italic leading-tight">
                     {project.demoLogin.note}
                  </p>
                </div>
              )}
            </div>
          )}

          {/* AI Content Zone */}
          <div className="glass-purple p-[24px] rounded-[12px]">
            <h2 className="text-[24px] font-bold mb-[8px] tracking-tight">{project.title}</h2>
            <div className="text-[14px] leading-[1.6] text-white/80 mb-[20px] font-medium prose prose-invert prose-sm max-w-none">
              <ReactMarkdown>{project.content}</ReactMarkdown>
            </div>
            <div className="flex flex-wrap gap-[8px] mb-[15px]">
              {project.techStack?.map((t, i) => (
                <span key={i} className="font-mono text-[11px] px-[10px] py-[4px] bg-glossy-purple/20 border border-glossy-purple rounded-[4px] text-[#E0A0FF]">
                  {t.name}
                </span>
              ))}
            </div>
            <div className="flex items-center gap-2 pt-4 border-t border-white/5 opacity-50">
              <Sparkles className="w-3 h-3 text-glossy-purple" />
              <span className="text-[9px] font-bold uppercase tracking-widest text-white">Enhanced by Gemini AI</span>
            </div>
          </div>

          {/* Stats Grid */}
          <div className="grid grid-cols-1 gap-[15px]">
            <div className="grid grid-cols-2 gap-[15px]">
              <motion.div 
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                className="bg-white/5 border border-white/10 p-[15px] rounded-[8px] relative overflow-hidden group"
              >
                <div className="absolute inset-0 bg-sharp-orange/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div className="text-[10px] uppercase text-text-dim mb-[5px] font-bold tracking-wider flex items-center gap-2">
                  <Activity className="w-3 h-3 text-sharp-orange animate-pulse" />
                  Project Pulse
                </div>
                <div className="text-[18px] font-bold font-mono text-glow-orange">
                  {project.inquiriesCount?.toLocaleString() || 0} Inquiries
                </div>
              </motion.div>
              <div className="bg-white/5 border border-white/10 p-[15px] rounded-[8px]">
                <div className="text-[10px] uppercase text-text-dim mb-[5px] font-bold tracking-wider">Status</div>
                <div className="text-[18px] font-bold font-mono text-[#00FF00]">LIVE</div>
              </div>
            </div>

            {/* Social Sharing */}
            <div className="bg-white/5 border border-white/10 p-4 rounded-[12px] flex items-center justify-between group">
              <span className="text-[10px] text-text-dim font-black uppercase tracking-widest flex items-center gap-2">
                <Share2 className="w-3 h-3 group-hover:text-sharp-orange transition-colors" /> Transmit Node
              </span>
              <div className="flex gap-4">
                <button 
                  onClick={() => handleCopy(shareUrl, 'share')}
                  className="text-white/40 hover:text-sharp-orange transition-colors duration-300"
                  title="Copy Node Link"
                >
                  {copiedField === 'share' ? <Check className="w-4 h-4" /> : <Copy className="w-4 h-4" />}
                </button>
                {shareLinks.map((s) => (
                  <a 
                    key={s.name}
                    href={s.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className={`text-white/40 transition-colors duration-300 ${s.color}`}
                    title={`Share on ${s.name}`}
                  >
                    <s.icon className="w-4 h-4" />
                  </a>
                ))}
              </div>
            </div>
          </div>

          {/* Performance Meter */}
          <motion.div 
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            className="bg-white/5 border border-white/10 rounded-[12px] p-[20px] glass-purple relative overflow-hidden group"
          >
            <div className="absolute -right-4 -top-4 w-24 h-24 bg-glossy-purple/10 blur-3xl rounded-full group-hover:bg-glossy-purple/20 transition-all duration-700"></div>
            <div className="flex justify-between items-end relative z-10 mb-4">
              <div className="flex flex-col">
                <span className="text-[10px] uppercase text-text-dim font-bold tracking-widest flex items-center gap-2">
                  <Zap className="w-3 h-3 text-glossy-purple" />
                  Live Performance Node
                </span>
                <span className="text-[10px] text-glossy-purple/70 font-mono mt-1">LATENCY: 14MS // THREADS: ACTIVE</span>
              </div>
              <span className="text-[24px] font-black font-mono text-glossy-purple text-glow-purple italic">
                {Math.max(project.performance?.speed || 0, project.performance?.security || 0)}%
              </span>
            </div>
            <div className="space-y-2 relative z-10">
              <div className="h-[6px] w-full bg-white/10 rounded-full overflow-hidden">
                <motion.div 
                  initial={{ width: 0 }}
                  animate={{ width: `${Math.max(project.performance?.speed || 0, project.performance?.security || 0)}%` }}
                  transition={{ duration: 1.5, ease: "easeOut" }}
                  className="meter-fill"
                ></motion.div>
              </div>
              <div className="flex justify-between text-[8px] font-mono text-text-dim uppercase tracking-tighter">
                <span>0.00ms</span>
                <span className="text-glossy-purple/50 animate-pulse">Optimum Sync Active</span>
                <span>100.00ms</span>
              </div>
            </div>
          </motion.div>

          {/* SEO Insights Section */}
          <div className="bg-white/5 border border-white/10 rounded-[12px] p-6 space-y-6 relative overflow-hidden group">
            <div className="absolute top-0 right-0 w-32 h-32 bg-sharp-orange/5 blur-3xl rounded-full -mr-16 -mt-16 group-hover:bg-sharp-orange/10 transition-all duration-700"></div>
            <div className="flex items-center justify-between border-b border-white/5 pb-3">
              <span className="text-[11px] font-black uppercase tracking-[0.3em] text-sharp-orange flex items-center gap-2">
                <SearchIcon className="w-3 h-3" /> SEO Insights
              </span>
              <div className="text-[9px] font-mono text-text-dim uppercase flex items-center gap-1">
                <span className="w-1 h-1 rounded-full bg-green-500 animate-pulse"></span>
                Status: Indexed
              </div>
            </div>
            
            <div className="space-y-4 relative z-10">
              <div className="space-y-1">
                <div className="flex items-center justify-between">
                  <span className="text-[9px] font-bold text-text-dim uppercase tracking-wider">Meta Title</span>
                  <button onClick={() => project.seoData?.metaTitle && handleCopy(project.seoData.metaTitle, 'title')} className="opacity-0 group-hover:opacity-100 transition-opacity">
                    {copiedField === 'title' ? <Check className="w-2.5 h-2.5 text-sharp-orange" /> : <Copy className="w-2.5 h-2.5 text-text-dim hover:text-white" />}
                  </button>
                </div>
                <p className="text-[11px] font-medium text-white/90 leading-tight border-l-2 border-sharp-orange/30 pl-3">
                  {project.seoData?.metaTitle || 'No Title Node'}
                </p>
              </div>

              <div className="space-y-1">
                <div className="flex items-center justify-between">
                  <span className="text-[9px] font-bold text-text-dim uppercase tracking-wider">Meta Description</span>
                  <button onClick={() => project.seoData?.metaDescription && handleCopy(project.seoData.metaDescription, 'desc')} className="opacity-0 group-hover:opacity-100 transition-opacity">
                    {copiedField === 'desc' ? <Check className="w-2.5 h-2.5 text-sharp-orange" /> : <Copy className="w-2.5 h-2.5 text-text-dim hover:text-white" />}
                  </button>
                </div>
                <p className="text-[11px] font-medium text-white/70 leading-relaxed italic border-l-2 border-white/10 pl-3">
                  {project.seoData?.metaDescription || 'No Description Mapped'}
                </p>
              </div>

              {project.seoData?.keywords && project.seoData.keywords.length > 0 && (
                <div className="space-y-2">
                  <span className="text-[9px] font-bold text-text-dim uppercase tracking-wider block">Keywords/Tags</span>
                  <div className="flex flex-wrap gap-1.5 pl-3">
                    {project.seoData.keywords.map((kw, i) => (
                      <span key={i} className="text-[9px] font-mono px-2 py-0.5 bg-white/5 border border-white/10 rounded text-text-dim flex items-center gap-1 hover:border-sharp-orange/40 hover:text-white transition-all cursor-default">
                        <Tag className="w-2 h-2" /> {kw}
                      </span>
                    ))}
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* WhatsApp CTA */}
          <button 
            onClick={handleWAClick}
            className="w-full bg-sharp-orange text-black py-[18px] rounded-[8px] font-extrabold text-[14px] uppercase tracking-[1px] flex items-center justify-center gap-[10px] transition-transform active:scale-95 group"
          >
            <MessageSquare className="w-5 h-5 transition-transform group-hover:scale-110" />
            {project.type === 'web' ? 'Get Site Like This' : 'Get App Like This'}
          </button>
        </div>
      </div>
    </div>
  );
}
