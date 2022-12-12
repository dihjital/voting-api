//
//  QuestionAndVote.swift
//  Voting
//
//  Created by Szabolcs Tóth on 16.10.2022.
//  Copyright © 2022 Szabolcs Tóth. All rights reserved.
//

import Foundation

struct QuestionAndVote: Identifiable {
    let id: UUID
    let questionText: String
    let numberOfVotes: Int
    let votes: [Vote]
}
