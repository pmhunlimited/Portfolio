
import { useState, useEffect, useMemo } from 'react';
import { collection, query, orderBy, onSnapshot, doc, getDoc } from 'firebase/firestore';
import { db } from '../lib/firebase';
import { Project } from '../types';
import { Link } from 'react-router-dom';
import { LayoutGrid, AppWindow, ArrowRight, ExternalLink, Activity, Sparkles, Search, X as CloseIcon, Pin, Image as ImageIcon, SortAsc, SortDesc, Filter, Calendar, Type } from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';
import MediaRenderer from '../components/MediaRenderer';

export default function Home() {
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState<'all' | 'web' | 'app'>('all');
  const [searchTerm, setSearchTerm] = useState('');
  const [sortBy, setSortBy] = useState<'newest' | 'oldest' | 'title_asc' | 'title_desc'>('newest');
  const [selectedTechs, setSelectedTechs] = useState<string[]>([]);
  
  // Settings state
  const [appTitle, setAppTitle] = useState('CYBER-PULSE');
  const [heroSubtext, setHeroSubtext] = useState('Sophisticated full-stack engineering. Powered by Gemini AI. Crafted for the elite digital frontier.');

  useEffect(() => {
    // Fetch Global Settings
    const fetchSettings = async () => {
      const docRef = doc(db, 'settings', 'global');
      const docSnap = await getDoc(docRef);
      if (docSnap.exists()) {
        const data = docSnap.data();
        if (data.appTitle) setAppTitle(data.appTitle);
        if (data.heroSubtext) setHeroSubtext(data.heroSubtext);
      }
    };
    fetchSettings();

    const q = query(collection(db, 'projects'), orderBy('createdAt', 'desc'));
    const unsubscribe = onSnapshot(q, (snapshot) => {
      const docs = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() } as Project));
      setProjects(docs);
      setLoading(false);
    });
    return () => unsubscribe();
  }, []);

  const allTechs = useMemo(() => {
    const techs = new Set<string>();
    projects.forEach(p => p.techStack?.forEach(t => techs.add(t.name)));
    return Array.from(techs).sort();
  }, [projects]);

  const filteredProjects = useMemo(() => {
    let result = projects.filter(p => {
      const typeMatch = filter === 'all' || p.type === filter;
      const searchLower = searchTerm.toLowerCase();
      const searchMatch = !searchTerm || 
        p.title.toLowerCase().includes(searchLower) || 
        p.content.toLowerCase().includes(searchLower) ||
        p.techStack.some(t => t.name.toLowerCase().includes(searchLower));
      
      const techMatch = selectedTechs.length === 0 || 
        selectedTechs.every(st => p.techStack?.some(t => t.name === st));

      return typeMatch && searchMatch && techMatch;
    });

    // Sorting
    result.sort((a, b) => {
      if (sortBy === 'newest') return (b.createdAt?.seconds || 0) - (a.createdAt?.seconds || 0);
      if (sortBy === 'oldest') return (a.createdAt?.seconds || 0) - (b.createdAt?.seconds || 0);
      if (sortBy === 'title_asc') return a.title.localeCompare(b.title);
      if (sortBy === 'title_desc') return b.title.localeCompare(a.title);
      return 0;
    });

    return result;
  }, [projects, filter, searchTerm, sortBy, selectedTechs]);

  const toggleTech = (tech: string) => {
    setSelectedTechs(prev => 
      prev.includes(tech) ? prev.filter(t => t !== tech) : [...prev, tech]
    );
  };

  const titleParts = appTitle.split('-').map(p => p.trim());
  
  const heroProjects = useMemo(() => {
    const pinned = projects.filter(p => p.isPinned);
    // If admin has pinned projects, show only those (up to 4)
    // Otherwise fallback to latest for a populated UI
    return pinned.length > 0 ? pinned.slice(0, 4) : projects.slice(0, 4);
  }, [projects]);

  if (loading) {
    return (
      <div className="py-20 flex justify-center">
        <div className="w-10 h-10 border-2 border-sharp-orange border-t-transparent rounded-full animate-spin"></div>
      </div>
    );
  }

  return (
    <div className="space-y-12">
      {/* Hero Section */}
      <section className="py-20 relative overflow-hidden">
        <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-sharp-orange/5 blur-[120px] rounded-full -z-10"></div>
        
        <div className="flex flex-col lg:flex-row items-center gap-12">
          {/* Main Hero Content */}
          <div className="flex-1 text-center lg:text-left space-y-8">
            <div className="flex flex-wrap items-center justify-center lg:justify-start gap-4">
              <motion.div 
                initial={{ opacity: 0, scale: 0.9 }}
                animate={{ opacity: 1, scale: 1 }}
                className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-[11px] font-bold tracking-[0.4em] uppercase text-text-dim"
              >
                <span className="w-1.5 h-1.5 rounded-full bg-sharp-orange neon-orange animate-pulse"></span>
                Intelligence Synced
              </motion.div>

              <motion.div 
                initial={{ opacity: 0, scale: 0.9 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ delay: 0.1 }}
                className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-[11px] font-bold tracking-[0.4em] uppercase text-glossy-purple"
              >
                <Sparkles className="w-3 h-3" />
                Live Hub Active
              </motion.div>
            </div>

            <motion.h1 
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="text-4xl sm:text-6xl md:text-7xl lg:text-8xl font-black tracking-tighter italic leading-[0.9] text-glow-orange uppercase"
            >
              {titleParts[0]} <br />
              <span className="text-sharp-orange">{titleParts.slice(1).join(' ') || 'PORTFOLIO.'}</span>
            </motion.h1>

            <motion.p 
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ delay: 0.3 }}
              className="text-text-dim max-w-2xl mx-auto lg:mx-0 text-lg leading-relaxed font-medium font-sans"
            >
              {heroSubtext}
            </motion.p>
          </div>

          {/* Recent Nodes Preview Grid */}
          <div className="w-full lg:w-[450px] grid grid-cols-2 gap-4">
            {heroProjects.length > 0 ? (
              heroProjects.map((proj, idx) => (
                <motion.div
                  key={proj.id}
                  initial={{ opacity: 0, x: 20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.2 + (idx * 0.1) }}
                >
                  <Link 
                    to={`/project/${proj.slug}`}
                    className="group block aspect-square bg-white/5 border border-white/10 rounded-xl overflow-hidden relative"
                  >
                    {proj.thumbnailUrl ? (
                      <MediaRenderer 
                        src={proj.thumbnailUrl} 
                        className="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                      />
                    ) : (
                      <div className="absolute inset-0 flex items-center justify-center bg-black/40">
                        <ImageIcon className="w-5 h-5 text-white/10" />
                      </div>
                    )}
                    <div className="absolute inset-x-0 bottom-0 p-3 bg-black/60 backdrop-blur-sm">
                      <div className="text-[9px] font-black uppercase text-white truncate">{proj.title}</div>
                      <div className="text-[7px] font-mono text-sharp-orange uppercase">Node: {idx + 1}</div>
                    </div>
                  </Link>
                </motion.div>
              ))
            ) : (
              Array.from({ length: 4 }).map((_, i) => (
                <div key={i} className="aspect-square bg-white/5 border border-dashed border-white/10 rounded-xl flex items-center justify-center">
                  <span className="text-[10px] text-text-dim font-mono">STANDBY...</span>
                </div>
              ))
            )}
          </div>
        </div>
      </section>

      {/* Filter Bar & Search */}
      <div className="flex flex-col space-y-8 border-b border-white/10 pb-8">
        <div className="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6">
          <div className="flex flex-wrap items-center gap-6">
            <div className="flex gap-4">
              {(['all', 'web', 'app'] as const).map((t) => (
                <button
                  key={t}
                  onClick={() => setFilter(t)}
                  className={`text-[12px] font-black uppercase tracking-[0.2em] transition-all relative pb-2 ${filter === t ? 'text-sharp-orange' : 'text-text-dim hover:text-white'}`}
                >
                  {t}
                  {filter === t && <motion.div layoutId="underline" className="absolute bottom-0 left-0 right-0 h-0.5 bg-sharp-orange" />}
                </button>
              ))}
            </div>

            <div className="h-4 w-px bg-white/10 hidden md:block"></div>

            <div className="flex items-center gap-4">
              <span className="text-[10px] font-bold text-text-dim uppercase tracking-widest flex items-center gap-2">
                <SortAsc className="w-3 h-3" /> Sort:
              </span>
              <select 
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value as any)}
                className="bg-white/5 border border-white/10 rounded-lg px-3 py-1 text-[11px] font-bold text-white uppercase outline-none focus:border-sharp-orange transition-colors cursor-pointer"
              >
                <option value="newest" className="bg-[#050505]">Newest First</option>
                <option value="oldest" className="bg-[#050505]">Oldest First</option>
                <option value="title_asc" className="bg-[#050505]">Alphabetical A-Z</option>
                <option value="title_desc" className="bg-[#050505]">Alphabetical Z-A</option>
              </select>
            </div>
          </div>
          <div className="text-[11px] font-mono text-text-dim uppercase tracking-[2px]">
            PB_CORE_LOG: {filteredProjects.length} NODES_ACTIVE
          </div>
        </div>

        {/* Tech Filtering Shell */}
        {allTechs.length > 0 && (
          <div className="space-y-3">
            <div className="flex items-center gap-2 text-[10px] font-bold text-text-dim uppercase tracking-widest">
              <Filter className="w-3 h-3" /> Filter by Stack:
            </div>
            <div className="flex flex-wrap gap-2">
              {allTechs.map(tech => (
                <button
                  key={tech}
                  onClick={() => toggleTech(tech)}
                  className={`px-3 py-1 rounded text-[10px] font-bold uppercase transition-all border ${selectedTechs.includes(tech) ? 'bg-glossy-purple border-glossy-purple text-white shadow-[0_0_10px_rgba(168,85,247,0.4)]' : 'bg-white/5 border-white/10 text-text-dim hover:text-white hover:border-white/30'}`}
                >
                  {tech}
                </button>
              ))}
              {selectedTechs.length > 0 && (
                <button 
                  onClick={() => setSelectedTechs([])}
                  className="px-3 py-1 rounded text-[10px] font-bold uppercase text-sharp-orange hover:underline transition-all"
                >
                  Clear All
                </button>
              )}
            </div>
          </div>
        )}

        {/* Global Search Interface */}
        <div className="relative group">
          <div className="absolute inset-0 bg-sharp-orange/5 blur-[20px] opacity-0 group-focus-within:opacity-100 transition-opacity duration-500 rounded-lg"></div>
          <div className="relative flex items-center bg-white/5 border border-white/10 rounded-xl overflow-hidden group-focus-within:border-sharp-orange transition-all duration-300">
            <Search className="ml-4 w-5 h-5 text-text-dim group-focus-within:text-sharp-orange transition-colors" />
            <input 
              type="text" 
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              placeholder="Filter nodes by title, content or tech..."
              className="w-full bg-transparent py-5 px-4 text-white font-medium outline-none placeholder:text-text-dim/50 text-sm tracking-wide lowercase italic"
            />
            <AnimatePresence>
              {searchTerm && (
                <motion.button 
                  initial={{ opacity: 0, x: 10 }}
                  animate={{ opacity: 1, x: 0 }}
                  exit={{ opacity: 0, x: 10 }}
                  onClick={() => setSearchTerm('')}
                  className="mr-4 p-1 rounded-full bg-white/10 hover:bg-white/20 transition-colors"
                >
                  <CloseIcon className="w-3 h-3 text-white" />
                </motion.button>
              )}
            </AnimatePresence>
          </div>
        </div>
      </div>

        {/* Project Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-10">
        {filteredProjects.map((project, idx) => (
          <motion.div
            key={project.id}
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: idx * 0.05 }}
          >
            <Link 
              to={`/project/${project.slug}`}
              className="group block bg-white/5 border border-white/10 rounded-xl overflow-hidden hover:border-sharp-orange transition-all duration-500 hover:-translate-y-2 relative"
            >
              <div className="aspect-[16/10] relative overflow-hidden bg-black/40">
                {project.thumbnailUrl ? (
                  <img 
                    src={project.thumbnailUrl} 
                    alt={project.title} 
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                    referrerPolicy="no-referrer"
                    loading="lazy"
                  />
                ) : (
                  <div className="w-full h-full flex items-center justify-center">
                    <ImageIcon className="w-8 h-8 text-white/5" />
                  </div>
                )}
                <div className="absolute inset-x-0 bottom-0 h-1/3 bg-black/60 backdrop-blur-sm px-8 pt-4">
                  <div className="text-[10px] font-black text-white uppercase tracking-widest">{project.title}</div>
                </div>
                
                <div className="absolute top-4 left-4 flex gap-2">
                  <span className={`px-2 py-1 rounded text-[9px] font-black uppercase tracking-widest border ${project.type === 'web' ? 'border-sharp-orange/30 text-sharp-orange' : 'border-glossy-purple/30 text-glossy-purple'}`}>
                    {project.type}
                  </span>
                  {project.isPinned && (
                    <span className="bg-sharp-orange text-black p-1 rounded-sm">
                      <Pin className="w-2.5 h-2.5 fill-current" />
                    </span>
                  )}
                </div>
              </div>

              <div className="p-8 space-y-4">
                <div className="space-y-1">
                  <h3 className="text-2xl font-black tracking-tight group-hover:text-sharp-orange transition-colors uppercase italic">
                    {project.title}
                  </h3>
                  <div className="flex gap-2">
                    {project.techStack?.slice(0, 2).map((t, i) => (
                      <span key={i} className="text-[10px] font-mono text-text-dim uppercase">/{t.name}</span>
                    ))}
                  </div>
                </div>

                <p className="text-sm text-text-dim line-clamp-2 leading-relaxed font-medium">
                  {project.seoData?.metaDescription}
                </p>
                
                <div className="pt-4 flex items-center justify-between border-t border-white/5">
                  <div className="flex items-center gap-2">
                    <Activity className="w-3 h-3 text-sharp-orange animate-pulse" />
                    <span className="text-[10px] font-bold text-text-dim uppercase tracking-widest italic leading-none">Pulse: Active</span>
                  </div>
                  <div className="text-[11px] font-mono text-sharp-orange font-bold uppercase flex items-center gap-1.5">
                    <span className="relative flex h-2 w-2">
                      <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-sharp-orange opacity-75"></span>
                      <span className="relative inline-flex rounded-full h-2 w-2 bg-sharp-orange"></span>
                    </span>
                    {project.inquiriesCount || 0} INQ
                  </div>
                </div>
              </div>
            </Link>
          </motion.div>
        ))}
      </div>

      {projects.length === 0 && (
        <div className="py-20 text-center bg-white/5 border border-white/10 rounded-xl">
          <p className="text-gray-500 font-mono text-xs tracking-widest uppercase">No projects mapped to grid.</p>
        </div>
      )}
    </div>
  );
}
