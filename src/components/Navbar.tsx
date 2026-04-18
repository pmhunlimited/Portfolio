
import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { User, signOut } from 'firebase/auth';
import { auth, signInWithGoogle, db } from '../lib/firebase';
import { doc, getDoc } from 'firebase/firestore';
import { Zap, Shield, LogOut, User as UserIcon, Menu, X } from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';

interface NavbarProps {
  user: User | null;
}

export default function Navbar({ user }: NavbarProps) {
  const [appTitle, setAppTitle] = useState('CYBER-PULSE');
  const [isMenuOpen, setIsMenuOpen] = useState(false);

  useEffect(() => {
    const fetchSettings = async () => {
      const docRef = doc(db, 'settings', 'global');
      const docSnap = await getDoc(docRef);
      if (docSnap.exists()) {
        const data = docSnap.data();
        if (data.appTitle) setAppTitle(data.appTitle);
      }
    };
    fetchSettings();
  }, []);

  return (
    <nav className="h-[60px] border-b border-sharp-orange nav-gradient sticky top-0 z-[100] flex items-center justify-between px-4 md:px-10">
      <Link to="/" className="flex items-center gap-2 group shrink-0">
        <span className="font-black text-[18px] md:text-[20px] tracking-[2px] text-sharp-orange uppercase">
          {appTitle}
        </span>
      </Link>

      <div className="hidden md:flex items-center gap-[30px]">
        <Link to="/" className="text-[13px] font-medium text-text-dim hover:text-sharp-orange transition-colors uppercase tracking-[1px]">PORTFOLIO</Link>
        
        {user?.email === 'philmorehost@gmail.com' && (
          <Link to="/admin" className="text-[13px] font-medium text-text-dim hover:text-sharp-orange transition-colors uppercase tracking-[1px]">ADMIN ENGINE</Link>
        )}

        {user ? (
          <div className="flex items-center gap-6">
            <div className="w-8 h-8 rounded-full border border-sharp-orange overflow-hidden">
              <img src={user.photoURL || ''} alt={user.displayName || ''} referrerPolicy="no-referrer" />
            </div>
            <button 
              onClick={() => signOut(auth)}
              className="text-text-dim hover:text-sharp-orange transition-colors p-1"
              title="Logout"
            >
              <LogOut className="w-4 h-4" />
            </button>
          </div>
        ) : (
          <button 
            onClick={signInWithGoogle}
            className="px-4 py-1.5 border border-sharp-orange text-sharp-orange hover:bg-sharp-orange hover:text-black transition-all text-[11px] font-bold uppercase tracking-widest"
          >
            LOGIN
          </button>
        )}
      </div>

      <button 
        onClick={() => setIsMenuOpen(!isMenuOpen)}
        className="md:hidden text-sharp-orange p-2"
      >
        {isMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
      </button>

      {/* Mobile Menu */}
      <AnimatePresence>
        {isMenuOpen && (
          <motion.div 
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -20 }}
            className="absolute top-[60px] left-0 right-0 bg-pitch-black border-b border-sharp-orange p-6 space-y-4 md:hidden flex flex-col items-center"
          >
            <Link 
              to="/" 
              onClick={() => setIsMenuOpen(false)}
              className="text-[14px] font-black text-white hover:text-sharp-orange uppercase tracking-widest"
            >
              PORTFOLIO
            </Link>
            
            {user?.email === 'philmorehost@gmail.com' && (
              <Link 
                to="/admin" 
                onClick={() => setIsMenuOpen(false)}
                className="text-[14px] font-black text-white hover:text-sharp-orange uppercase tracking-widest"
              >
                ADMIN ENGINE
              </Link>
            )}

            {user ? (
              <div className="flex flex-col items-center gap-4 pt-4 border-t border-white/10 w-full">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-full border border-sharp-orange overflow-hidden">
                    <img src={user.photoURL || ''} alt={user.displayName || ''} referrerPolicy="no-referrer" />
                  </div>
                  <span className="text-white text-xs font-mono">{user.displayName}</span>
                </div>
                <button 
                  onClick={() => {
                    signOut(auth);
                    setIsMenuOpen(false);
                  }}
                  className="w-full py-3 border border-red-500/30 text-red-500 font-bold uppercase tracking-widest text-[11px]"
                >
                  LOGOUT
                </button>
              </div>
            ) : (
              <button 
                onClick={() => {
                  signInWithGoogle();
                  setIsMenuOpen(false);
                }}
                className="w-full py-3 bg-sharp-orange text-black font-black uppercase tracking-widest text-[11px]"
              >
                SIGN IN
              </button>
            )}
          </motion.div>
        )}
      </AnimatePresence>
    </nav>
  );
}
