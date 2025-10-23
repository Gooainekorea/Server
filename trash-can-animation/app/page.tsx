"use client"

import { useState } from "react"
import { motion, AnimatePresence } from "framer-motion"
import { Button } from "@/components/ui/button"
import { Trash2, AlertTriangle } from "lucide-react"

export default function TrashCanPage() {
  const [filledAmount, setFilledAmount] = useState(0)
  const [fallingTrash, setFallingTrash] = useState<number[]>([])
  const [trashIdCounter, setTrashIdCounter] = useState(0)
  const [isLidOpen, setIsLidOpen] = useState(false)
  const [isShaking, setIsShaking] = useState(false)

  const fillTrashCan = () => {
    if (filledAmount >= 100) {
      return
    }

    setIsLidOpen(true)

    // 쓰레기 아이템 생성
    const newTrashId = trashIdCounter
    setTrashIdCounter((prev) => prev + 1)
    setFallingTrash((prev) => [...prev, newTrashId])

    setTimeout(() => {
      setIsShaking(true)
      setFilledAmount((prev) => Math.min(prev + 10, 100))
      setFallingTrash((prev) => prev.filter((id) => id !== newTrashId))

      // Stop shaking after animation
      setTimeout(() => {
        setIsShaking(false)
      }, 500)
    }, 800)

    setTimeout(() => {
      setIsLidOpen(false)
    }, 1200)
  }

  const emptyTrash = () => {
    setFilledAmount(0)
    setFallingTrash([])
  }

  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-white p-8">
      <div className="max-w-md w-full space-y-8">
        <div className="text-center space-y-2">
          <h1 className="text-4xl font-bold text-slate-900">쓰레기통 모니터링</h1>
          <p className="text-slate-600">버튼을 클릭해서 쓰레기를 넣어보세요</p>
        </div>

        <div className="relative flex justify-center">
          <AnimatePresence>
            {fallingTrash.map((id) => (
              <motion.div
                key={id}
                initial={{ y: -120, x: 0, opacity: 1, rotate: 0, scale: 1 }}
                animate={{
                  y: 180,
                  x: [0, Math.random() * 20 - 10, 0],
                  rotate: [0, Math.random() * 180, Math.random() * 360],
                  scale: [1, 0.9, 0.7],
                  opacity: [1, 1, 0.5, 0],
                }}
                exit={{ opacity: 0 }}
                transition={{
                  duration: 0.8,
                  ease: [0.4, 0, 0.6, 1],
                }}
                className="absolute z-30 pointer-events-none"
                style={{ left: "50%", marginLeft: "-12px" }}
              >
                <div className="w-6 h-6 bg-slate-400 rounded-sm shadow-lg" />
              </motion.div>
            ))}
          </AnimatePresence>

          <div className="relative">
            {/* Lid */}
            <motion.div
              animate={{
                rotateX: isLidOpen ? -60 : 0,
                y: isLidOpen ? -10 : 0,
              }}
              transition={{
                duration: 0.3,
                ease: "easeInOut",
              }}
              style={{
                transformOrigin: "bottom center",
                transformStyle: "preserve-3d",
              }}
              className="absolute -top-6 left-1/2 -translate-x-1/2 w-48 h-8 bg-white border-4 border-slate-900 rounded-t-xl z-20"
            >
              {/* Lid handle */}
              <div className="absolute top-1 left-1/2 -translate-x-1/2 w-12 h-3 bg-white border-2 border-slate-900 rounded-full" />

              {filledAmount >= 70 && !isLidOpen && (
                <div className="absolute -top-8 left-1/2 -translate-x-1/2 flex gap-3">
                  <motion.div
                    animate={{ y: [-5, -15, -5], opacity: [0.5, 1, 0.5] }}
                    transition={{ duration: 2, repeat: Number.POSITIVE_INFINITY }}
                    className="w-1 h-8 border-l-4 border-slate-400 rounded-full"
                    style={{ borderStyle: "dashed" }}
                  />
                  <motion.div
                    animate={{ y: [-5, -15, -5], opacity: [0.5, 1, 0.5] }}
                    transition={{ duration: 2, repeat: Number.POSITIVE_INFINITY, delay: 0.3 }}
                    className="w-1 h-8 border-l-4 border-slate-400 rounded-full"
                    style={{ borderStyle: "dashed" }}
                  />
                </div>
              )}
            </motion.div>

            <motion.div
              animate={
                isShaking
                  ? {
                      rotate: [0, -2, 2, -2, 2, 0],
                      x: [0, -2, 2, -2, 2, 0],
                    }
                  : {}
              }
              transition={{
                duration: 0.5,
                ease: "easeInOut",
              }}
              className="relative w-48 h-64 bg-white border-4 border-slate-900 rounded-lg overflow-hidden"
            >
              {/* Vertical lines on trash can */}
              <div className="absolute top-0 bottom-0 left-8 w-0.5 bg-slate-900 opacity-30" />
              <div className="absolute top-0 bottom-0 right-8 w-0.5 bg-slate-900 opacity-30" />

              <motion.div
                className="absolute bottom-0 left-0 right-0 bg-slate-300 rounded-t-lg"
                initial={{ height: 0 }}
                animate={{
                  height: `${filledAmount}%`,
                }}
                transition={{
                  duration: 0.5,
                  ease: "easeOut",
                }}
              >
                {/* Wavy top surface */}
                <svg
                  className="absolute -top-2 left-0 right-0 w-full"
                  height="10"
                  viewBox="0 0 200 10"
                  preserveAspectRatio="none"
                >
                  <path d="M0,5 Q25,2 50,5 T100,5 T150,5 T200,5 L200,10 L0,10 Z" fill="#cbd5e1" />
                </svg>

                {filledAmount > 0 && (
                  <div className="absolute inset-0">
                    {[...Array(Math.floor(filledAmount / 5))].map((_, i) => (
                      <div
                        key={i}
                        className="absolute w-2 h-2 bg-slate-600 rounded-full"
                        style={{
                          left: `${15 + ((i * 23) % 70)}%`,
                          top: `${10 + ((i * 17) % 80)}%`,
                        }}
                      />
                    ))}
                  </div>
                )}
              </motion.div>

              <div className="absolute inset-0 flex items-center justify-center z-10">
                <motion.div
                  key={filledAmount}
                  initial={{ scale: 1.3, opacity: 0 }}
                  animate={{ scale: 1, opacity: 1 }}
                  className="text-6xl font-bold text-slate-900"
                >
                  {filledAmount}
                </motion.div>
              </div>
            </motion.div>

            {filledAmount >= 100 && (
              <motion.div
                initial={{ opacity: 0, scale: 0 }}
                animate={{ opacity: 1, scale: 1 }}
                className="absolute -right-12 top-1/2 -translate-y-1/2"
              >
                <div className="relative">
                  <AlertTriangle className="w-12 h-12 text-slate-900 fill-white" strokeWidth={3} />
                  <div className="absolute inset-0 flex items-center justify-center">
                    <div className="text-2xl font-bold text-slate-900">!</div>
                  </div>
                </div>
              </motion.div>
            )}

            {isShaking && (
              <>
                <motion.div
                  initial={{ opacity: 0 }}
                  animate={{ opacity: [0, 1, 0] }}
                  transition={{ duration: 0.5 }}
                  className="absolute -left-6 top-1/2 -translate-y-1/2"
                >
                  <div className="flex flex-col gap-1">
                    {[...Array(3)].map((_, i) => (
                      <div key={i} className="w-4 h-0.5 bg-slate-900 rounded-full" />
                    ))}
                  </div>
                </motion.div>
                <motion.div
                  initial={{ opacity: 0 }}
                  animate={{ opacity: [0, 1, 0] }}
                  transition={{ duration: 0.5 }}
                  className="absolute -right-6 top-1/2 -translate-y-1/2"
                >
                  <div className="flex flex-col gap-1">
                    {[...Array(3)].map((_, i) => (
                      <div key={i} className="w-4 h-0.5 bg-slate-900 rounded-full" />
                    ))}
                  </div>
                </motion.div>
              </>
            )}
          </div>
        </div>

        {/* 버튼들 */}
        <div className="flex gap-4">
          <Button
            onClick={fillTrashCan}
            disabled={filledAmount >= 100}
            className="flex-1 h-14 text-lg font-semibold bg-slate-900 hover:bg-slate-700 disabled:bg-slate-400 text-white"
          >
            <Trash2 className="mr-2 h-5 w-5" />
            쓰레기 넣기
          </Button>
          <Button
            onClick={emptyTrash}
            variant="outline"
            className="flex-1 h-14 text-lg font-semibold border-2 border-slate-900 hover:bg-slate-100 bg-white text-slate-900"
          >
            쓰레기통 비우기
          </Button>
        </div>

        {/* 상태 표시 */}
        <div className="text-center text-sm text-slate-600">
          {filledAmount < 100 ? (
            <p>남은 용량: {100 - filledAmount}%</p>
          ) : (
            <p className="text-slate-900 font-semibold">쓰레기통을 비워주세요!</p>
          )}
        </div>
      </div>
    </div>
  )
}
