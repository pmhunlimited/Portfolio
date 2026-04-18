
import React from 'react';

interface MediaRendererProps {
  src: string;
  className?: string;
  style?: React.CSSProperties;
  alt?: string;
  referrerPolicy?: React.HTMLAttributeReferrerPolicy;
  loading?: 'lazy' | 'eager';
  onClick?: () => void;
}

export const isVideo = (url: string): boolean => {
  if (!url) return false;
  // Check for data URI
  if (url.startsWith('data:video/')) return true;
  // Check common video extensions
  const videoExtensions = ['.mp4', '.webm', '.ogg', '.mov'];
  return videoExtensions.some(ext => url.toLowerCase().split('?')[0].endsWith(ext));
};

const MediaRenderer: React.FC<MediaRendererProps> = ({ 
  src, 
  className = "w-full h-full object-cover", 
  style,
  alt = "", 
  referrerPolicy = "no-referrer",
  loading = "lazy",
  onClick
}) => {
  if (!src) return null;

  if (isVideo(src)) {
    return (
      <video
        src={src}
        className={className}
        style={style}
        autoPlay
        loop
        muted
        playsInline
        onClick={onClick}
      />
    );
  }

  return (
    <img
      src={src}
      className={className}
      style={style}
      alt={alt}
      referrerPolicy={referrerPolicy}
      loading={loading}
      onClick={onClick}
    />
  );
};

export default MediaRenderer;
