import { useState, useCallback } from 'react';

export type QRType = 'clock-in' | 'clock-out';
export type QRStatus = 'active' | 'used' | 'expired' | 'loading' | 'error';

export interface QRData {
  token: string;
  type: QRType;
  expiresAt: number;
  createdAt: number;
}

const generateTokenAPI = async (type: QRType): Promise<{ token: string; expiresIn: number }> => {
    await new Promise(resolve => setTimeout(resolve, 800));
      if (Math.random() < 0.1) {
    throw new Error('Network error: Failed to generate QR token');
  }
   
    const token = `${type}-${Date.now()}-${Math.random().toString(36).substring(2, 15)}`;
  const expiresIn = 60; // 60 seconds expiry
  
  return { token, expiresIn };
};

export const useQRCode = () => {
  const [qrData, setQRData] = useState<QRData | null>(null);
  const [status, setStatus] = useState<QRStatus>('loading');
  const [errorMessage, setErrorMessage] = useState<string>('');
  
  const generateQR = useCallback(async (type: QRType) => {
    setStatus('loading');
    setErrorMessage('');
    
    try {
      const { token, expiresIn } = await generateTokenAPI(type);
      const expiresAt = Date.now() + (expiresIn * 1000);

      setQRData({
        token,
        type,
        expiresAt,
        createdAt: Date.now(),
      });
      setStatus('active');

       const timer = setTimeout(() => {
        setStatus(prev => prev === 'active' ? 'expired' : prev);
      }, expiresIn * 1000);
      
      return () => clearTimeout(timer);
    } catch (error) {
      setStatus('error');
      setErrorMessage(error instanceof Error ? error.message : 'Failed to generate QR code');
      setQRData(null);
    }
  }, []);
  
  const markAsUsed = useCallback(() => {
    if (status === 'active') {
      setStatus('used');
    }

    }, [status]);
  
  const reset = useCallback(() => {
    setQRData(null);
    setStatus('loading');
    setErrorMessage('');
  }, []);
  
  return { qrData, status, errorMessage, generateQR, markAsUsed, reset };
};