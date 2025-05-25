import { useState, useEffect, useCallback } from 'react';

export default function useImageSlider(images = [], autoPlay = true, intervalTime = 3000) {
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  const [isPlaying, setIsPlaying] = useState(autoPlay);

  const changeImage = useCallback((next = true) => {
    setCurrentImageIndex(prevIndex =>
      next
        ? (prevIndex + 1) % images.length
        : (prevIndex - 1 + images.length) % images.length
    );
  }, [images.length]);

  const startSlider = useCallback(() => {
    setIsPlaying(true);
  }, []);

  const stopSlider = useCallback(() => {
    setIsPlaying(false);
  }, []);

  useEffect(() => {
    let interval;
    if (isPlaying && images.length > 0) {
      interval = setInterval(() => changeImage(true), intervalTime);
    }
    return () => clearInterval(interval);
  }, [isPlaying, changeImage, images.length, intervalTime]);

  return {
    currentImageIndex,
    changeImage,
    startSlider,
    stopSlider,
    isPlaying,
  };
}
