
export interface TechStack {
  name: string;
  icon?: string;
}

export interface SEOData {
  metaTitle: string;
  metaDescription: string;
  keywords: string[];
}

export interface AccessPoint {
  url?: string;
  username?: string;
  password?: string;
  directLoginUrl?: string;
}

export interface Project {
  id: string;
  title: string;
  slug: string;
  content: string; // AI-written Power Pitch
  techStack: TechStack[];
  url: string;
  thumbnailUrl: string;
  galleryImages?: string[]; // Up to 5 images
  demoLogin?: {
    username?: string;
    password?: string;
    note?: string;
  };
  accessPoints?: {
    superAdmin?: AccessPoint;
    admin?: AccessPoint;
    user?: AccessPoint;
  };
  type: 'web' | 'app';
  waMessage: string;
  seoData: SEOData;
  performance?: {
    speed: number;
    security: number;
  };
  inquiriesCount: number;
  isPinned?: boolean;
  createdAt: any;
}
