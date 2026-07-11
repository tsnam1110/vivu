import type { CSSProperties } from 'react';
import type { AvatarFrame } from '../api/resources';
import './avatar-frames.css';

const sizeMap = {
  sm: 40,
  md: 64,
  lg: 96,
  xl: 128,
} as const;

type Size = keyof typeof sizeMap;

interface Props {
  frame: Pick<AvatarFrame, 'effect_type' | 'effect_config' | 'show_badge' | 'css_variables'>;
  size?: Size;
  imageUrl?: string | null;
  initials?: string;
}

function buildCssVars(frame: Props['frame']): CSSProperties {
  if (frame.css_variables) {
    return frame.css_variables as CSSProperties;
  }

  const cfg = frame.effect_config || {};
  const colors = cfg.colors?.length ? cfg.colors : ['#d6d3d1', '#a8a29e', '#78716c'];
  const c1 = colors[0] ?? '#d6d3d1';
  const c2 = colors[1] ?? c1;
  const c3 = colors[2] ?? c2;
  const thickness = Math.min(8, Math.max(1, cfg.thickness ?? 3));
  const speed = Math.min(12000, Math.max(800, cfg.speed_ms ?? 3000));
  const intensity = Math.min(1, Math.max(0.1, cfg.intensity ?? 0.6));

  return {
    ['--af-c1' as string]: c1,
    ['--af-c2' as string]: c2,
    ['--af-c3' as string]: c3,
    ['--af-gradient' as string]: `linear-gradient(135deg, ${colors.slice(0, 4).join(', ')})`,
    ['--af-thickness' as string]: `${thickness}px`,
    ['--af-speed' as string]: `${speed}ms`,
    ['--af-intensity' as string]: String(intensity),
  };
}

export default function AvatarFramePreview({
  frame,
  size = 'md',
  imageUrl,
  initials = 'VV',
}: Props) {
  const px = sizeMap[size];
  const style = buildCssVars(frame);

  return (
    <div className="af-root" style={{ position: 'relative', display: 'inline-flex' }}>
      <div className={`af-frame af-${frame.effect_type}`} style={style}>
        <div
          className="af-inner"
          style={{
            width: px,
            height: px,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontWeight: 700,
            fontSize: px * 0.28,
            color: '#fff',
            background: imageUrl ? undefined : 'linear-gradient(135deg, #14b8a6, #0891b2)',
          }}
        >
          {imageUrl ? (
            <img
              src={imageUrl}
              alt=""
              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
            />
          ) : (
            initials
          )}
        </div>
      </div>
      {frame.show_badge ? (
        <span
          className="af-badge"
          title="Premium"
          style={{
            position: 'absolute',
            right: -2,
            bottom: -2,
            width: 18,
            height: 18,
            borderRadius: '999px',
            background: 'linear-gradient(135deg, #fcd34d, #f59e0b)',
            color: '#451a03',
            fontSize: 10,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            outline: '2px solid #fff',
          }}
        >
          ✦
        </span>
      ) : null}
    </div>
  );
}
